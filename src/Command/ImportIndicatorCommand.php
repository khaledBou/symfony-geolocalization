<?php

namespace App\Command;

use App\Entity\Commune;
use App\Entity\Indicator;
use App\Entity\Quartier;
use App\Service\KelQuartierProApiHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande d'import des indicateurs KelQuartier.
 *
 * @see http://pro.kelquartier.com/documentation
 */
class ImportIndicatorCommand extends Command
{
    /**
     * À chaque identifiant d'indicateur KelQuartier
     * correspond un type d'indicateur dans notre application.
     *
     * @var array
     */
    const STATS_INDICATORS = [
        'descriptif' => Indicator\TextIndicator::class,
        'photo_url' => Indicator\TextIndicator::class,
        'photo_title' => Indicator\StringIndicator::class,
        'prix_appart_moyen' => Indicator\IntIndicator::class,
        'prix_appart_bas' => Indicator\IntIndicator::class,
        'prix_appart_haut' => Indicator\IntIndicator::class,
        'prix_maison_moyen' => Indicator\IntIndicator::class,
        'prix_maison_bas' => Indicator\IntIndicator::class,
        'prix_maison_haut' => Indicator\IntIndicator::class,
        '3' => Indicator\StringIndicator::class,
        '145' => Indicator\IntIndicator::class,
        '19' => Indicator\RatioIndicator::class,
        '21' => Indicator\RatioIndicator::class,
        '142' => Indicator\RatioIndicator::class,
        '143' => Indicator\RatioIndicator::class,
        '232' => Indicator\IntIndicator::class,
        '110' => Indicator\StringIndicator::class,
        '34' => Indicator\StringIndicator::class,
        '216' => Indicator\StringIndicator::class,
        '217' => Indicator\StringIndicator::class,
        '11' => Indicator\IntIndicator::class,
        '26' => Indicator\IntIndicator::class,
        '29' => Indicator\StringIndicator::class,
        '53' => Indicator\RatioIndicator::class,
        '7' => Indicator\IntIndicator::class,
        '135' => Indicator\RatioIndicator::class,
        '136' => Indicator\RatioIndicator::class,
        '137' => Indicator\RatioIndicator::class,
    ];

    protected static $defaultName = 'app:indicator:import';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $areaRepositories;

    /**
     * @var array
     */
    private $indicatorRepositories;

    /**
     * @var KelQuartierProApiHelper
     */
    private $kelQuartierProApiHelper;

    /**
     * @param EntityManagerInterface  $em
     * @param KelQuartierProApiHelper $kelQuartierProApiHelper
     */
    public function __construct(EntityManagerInterface $em, KelQuartierProApiHelper $kelQuartierProApiHelper)
    {
        parent::__construct();

        $this->em = $em;
        $this->areaRepositories = [
            Commune::class => $em->getRepository(Commune::class),
            Quartier::class => $em->getRepository(Quartier::class),
        ];
        $this->indicatorRepositories = [
            Indicator\IntIndicator::class => $em->getRepository(Indicator\IntIndicator::class),
            Indicator\RatioIndicator::class => $em->getRepository(Indicator\RatioIndicator::class),
            Indicator\StringIndicator::class => $em->getRepository(Indicator\StringIndicator::class),
            Indicator\TextIndicator::class => $em->getRepository(Indicator\TextIndicator::class),
        ];
        $this->kelQuartierProApiHelper = $kelQuartierProApiHelper;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Importe les indicateurs à partir de l\'API KelQuartier')
            ->setHelp('Parcourt les zones géographiques de la base de données et importe, pour les communes et quartiers, les indicateurs à partir de l\'API KelQuartier.')
            ->addArgument('age', InputArgument::OPTIONAL, 'Âge des indicateurs à partir duquel une mise à jour est nécessaire (en jours)', 30)
            ->addArgument('limit', InputArgument::OPTIONAL, 'Nombre de communes pour lesquelles mettre à jour les indicateurs (laisser vide pour désactiver la limite)', null)
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title(sprintf('[%s] Début d\'import des indicateurs', (new \DateTime())->format('d/m/Y H:i:s')));

        $age = $input->getArgument('age');
        $limit = $input->getArgument('limit');

        // Nombre d'indicateurs importés
        $persistedCount = 0;

        // Boucle sur des types de zones géographiques
        foreach ([
            Commune::class,
            Quartier::class,
        ] as $areaClassName) {
            $coordinatesToUpdate = $this->areaRepositories[$areaClassName]->getCoordinatesForIndicatorsToUpdate($age, null);
            $coordinates = $this->areaRepositories[$areaClassName]->getCoordinatesForIndicatorsToUpdate($age, $limit);

            ProgressBar::setFormatDefinition('custom', '%message%'.PHP_EOL.'%current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s% %memory:6s%)'.PHP_EOL);
            $progressBar = new ProgressBar($output, count($coordinates));
            $progressBar->setFormat('custom');
            $progressBar->setMessage(sprintf(
                'Import des indicateurs pour les zones géographique de type %s (tronçon de %d sur %d zones restantes à mettre à jour)',
                $areaClassName,
                count($coordinates),
                count($coordinatesToUpdate)
            ));

            foreach ($coordinates as $c) {
                $id = $c['id'];
                list($lng, $lat) = $c['coordinates'];

                $progressBar->advance();

                $portrait = $this->kelQuartierProApiHelper->call('getPortrait.php', [
                    'lon' => $lng,
                    'lat' => $lat,
                    'bv' => Commune::class === $areaClassName ? 1 : 0,
                ]);

                $prix = $this->kelQuartierProApiHelper->call('getPolygonePrix.php', [
                    'lon' => $lng,
                    'lat' => $lat,
                ]);

                // Simule des statistiques pour pouvoir utiliser la mécanique ci-après.
                if (!empty($portrait['Polygone'])) {
                    $portrait['Stats']['descriptif'] = [
                        'id' => 'descriptif',
                        'carte_stat' => $portrait['Polygone'][0]['descriptif'],
                    ];
                }
                if (!empty($portrait['Photos'])) {
                    $portrait['Stats']['photo_url'] = [
                        'id' => 'photo_url',
                        'carte_stat' => $portrait['Photos'][0]['photo_url'],
                    ];
                    $portrait['Stats']['photo_title'] = [
                        'id' => 'photo_title',
                        'carte_stat' => $portrait['Photos'][0]['photo_title'],
                    ];
                }
                foreach ([
                    'prix_appart_moyen',
                    'prix_appart_bas',
                    'prix_appart_haut',
                    'prix_maison_moyen',
                    'prix_maison_bas',
                    'prix_maison_haut',
                ] as $p) {
                    if (!empty($prix[$p])) {
                        $portrait['Stats'][$p] = [
                            'id' => $p,
                            'carte_stat' => $prix[$p],
                        ];
                    }
                }

                $area = $this->areaRepositories[$areaClassName]->find($id);

                if (!empty($portrait['Stats'])) {
                    foreach ($portrait['Stats'] as $stat) {
                        $statId = $stat['id'];

                        if (isset(self::STATS_INDICATORS[$statId])) {
                            $indicatorClass = self::STATS_INDICATORS[$statId];

                            $value = $this->parseValue($stat['carte_stat'], $indicatorClass);
                            $kelQuartierId = (string) $statId;

                            $indicator = $this->indicatorRepositories[$indicatorClass]->findOneBy([
                                'area' => $area,
                                'kelQuartierId' => $kelQuartierId,
                            ]);

                            if (null === $indicator) {
                                $indicator = new $indicatorClass();
                            }

                            $indicator
                                ->setArea($area)
                                ->setValue($value)
                                ->setKelQuartierId($kelQuartierId)
                                ->setDate(new \DateTime())
                            ;

                            $this->em->persist($indicator);
                            $persistedCount++;
                        }
                    }
                }

                if (0 === $persistedCount % 10) {
                    $this->em->flush();
                }
            }

            $this->em->flush();
        }

        $progressBar->finish();

        $io->newLine();
        $io->newLine();
        $io->writeln(sprintf('Nombre d\'indicateurs importés : %s', $persistedCount));
        $io->title(sprintf('[%s] Fin d\'import des indicateurs', (new \DateTime())->format('d/m/Y H:i:s')));

        return 0;
    }

    /**
     * Parse la valeur $value pour la formater
     * comme attendue par l'indicateur $indicatorClass.
     *
     * @param mixed  $value
     * @param string $indicatorClass
     *
     * @return mixed $value
     */
    private function parseValue($value, string $indicatorClass)
    {
        switch ($indicatorClass) {
            case Indicator\IntIndicator::class:
            case Indicator\RatioIndicator::class:
                $value = (int) $value;
                break;
            case Indicator\TextIndicator::class:
            case Indicator\StringIndicator::class:
                $value = str_replace([
                    '<br>',
                    '<br/>',
                    '<br />',
                ], "\n", $value);
                break;
        }

        return $value;
    }

    /**
     * Formate un nombre d'octets.
     *
     * @param int $bytes
     *
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'];

        return $bytes > 0 ? sprintf('%s %s', @round($bytes / pow(1024, ($u = floor(log($bytes, 1024)))), 2), $units[$u]) : '0 o';
    }
}
