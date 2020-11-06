<?php

namespace App\Command;

use App\Entity\Indicator\AbstractIndicator;
use App\Entity\Indicator\IndicatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Commande d'export des données pour la page d'accueil du site Paradissimmo.
 */
class ParadissimmoHomepageDataExportCommand extends Command
{
    /**
     * Le chemin du répertoire où déposer les fichiers d'export,
     * à partir de la racine.
     *
     * @var string
     */
    const EXPORT_PATH = 'var/export';

    /**
     * URL de l'API à interroger pour récupérer les données.
     *
     * @var string
     */
    const API_URL = 'https://api.geo.oryx-immobilier.com';

    // @var string
    protected static $defaultName = 'app:paradissimmo:homepage-data:export';

    /**
     * @var FileSystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @param EntityManagerInterface $em
     * @param ParameterBagInterface  $parameterBag
     */
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        parent::__construct();

        $this->em = $em;
        $this->fileSystem = new Filesystem();
        $this->projectDirectory = $parameterBag->get('kernel.project_dir');
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Exporte les données pour la page d\'accueil du site Paradissimmo, au format JSON');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title(sprintf('[%s] Début d\'export des données pour la page d\'accueil du site Paradissimmo', (new \DateTime())->format('d/m/Y H:i:s')));

        $url = sprintf('%s/graphql', self::API_URL);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTREDIR => CURL_REDIR_POST_ALL,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json', // ce qu'on envoie
                'Accept: application/ld+json', // ce qu'on attend en retour
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'query' => '{
                    firstTowns: communes(order: {population: "desc"}, exists: {population: true}, first: 12) {
                        edges {
                            node {
                                _id
                                id
                                name: nom
                                slug: alias
                                postalCodes: codesPostaux
                                postalCode: codePostal
                            }
                        }
                    }
                    mostTowns: communes(code_list: ["31555", "21231", "69266", "54395", "51454", "35238", "29019", "33063", "06088", "44109", "59350", "34172", "14118", "86194", "25056", "92012", "72181", "17300", "33281", "76540", "06029", "06004", "45234", "49007", "63113", "92004", "30189", "34003", "87085", "18033", "64445"]) {
                        edges {
                            node {
                                _id
                                id: code
                                slug: alias
                                name: nom
                                postalCodes: codesPostaux
                                postalCode: codePostal
                            }
                        }
                    }
                }',
            ]),
        ]);
        $rawResponse = curl_exec($ch);
        curl_close($ch);

        if (!$rawResponse) {
            throw new \Exception(sprintf('Failed to get response from %s', $url));
        }

        $response = json_decode($rawResponse, true);

        if (!empty($response['data'])) {
            /**
             * Identifiants des communes retournées par l'appel API,
             * en vue de récupérer les indicateurs associés.
             *
             * @var int[]
             */
            $communeIds = [];

            // Remplissage de $communeIds
            foreach ([
                'firstTowns',
                'mostTowns',
            ] as $query) {
                foreach ($response['data'][$query]['edges'] as $edge) {
                    $communeId = $edge['node']['_id'];
                    $communeIds[] = $communeId;
                }
            }

            /**
             * Indicateurs 'prix_appart_moyen' et 'prix_maison_moyen'
             * associés aux communes retournées par l'appel API.
             *
             * @var IndicatorInterface[]
             */
            $indicators = $this
                ->em
                ->getRepository(AbstractIndicator::class)
                ->findBy([
                    'area' => $communeIds,
                    'kelQuartierId' => [
                        'prix_appart_moyen',
                        'prix_maison_moyen',
                    ],
                ])
            ;

            /**
             * Indicateurs classés par zone géographique.
             *
             * @var[]
             */
            $indicatorsValueByAreaId = [];

            // Remplissage de $indicatorsValueByAreaId
            foreach ($indicators as $indicator) {
                $areaId = $indicator->getArea()->getId();
                $kelQuartierId = $indicator->getKelQuartierId();

                if (!isset($indicatorsValueByAreaId[$areaId])) {
                    $indicatorsValueByAreaId[$areaId] = [];
                }
                if (!isset($indicatorsValueByAreaId[$areaId][$kelQuartierId])) {
                    $indicatorsValueByAreaId[$areaId][$kelQuartierId] = [];
                }

                $indicatorsValueByAreaId[$areaId][$kelQuartierId] = $indicator->getValue();
            }

            // Fusion de $indicatorsValueByAreaId dans la réponse de l'API $response
            foreach ([
                'firstTowns',
                'mostTowns',
            ] as $query) {
                foreach ($response['data'][$query]['edges'] as $key => $edge) {
                    $areaId = $edge['node']['_id'];
                    $response['data'][$query]['edges'][$key]['node']['indicators'] = [
                        'prix_appart_moyen' => isset($indicatorsValueByAreaId[$areaId]['prix_appart_moyen']) ? $indicatorsValueByAreaId[$areaId]['prix_appart_moyen'] : null,
                        'prix_maison_moyen' => isset($indicatorsValueByAreaId[$areaId]['prix_maison_moyen']) ? $indicatorsValueByAreaId[$areaId]['prix_maison_moyen'] : null,
                    ];
                }
            }
        } else {
            throw new \Exception(sprintf('Failed to get data from %s : %s', $url, $rawResponse));
        }

        // Génération d'un fichier JSON
        $filename = 'paradissimmo/homepage-data.json';
        $filepath = sprintf(
            '%s/%s/%s',
            $this->projectDirectory,
            self::EXPORT_PATH,
            $filename
        );

        $this->fileSystem->dumpFile($filepath, json_encode($response, JSON_UNESCAPED_UNICODE));
        $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));

        $io->title(sprintf('[%s] Fin d\'export des données pour la page d\'accueil du site Paradissimmo', (new \DateTime())->format('d/m/Y H:i:s')));

        return 0;
    }
}
