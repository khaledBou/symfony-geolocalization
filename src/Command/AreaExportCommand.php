<?php

namespace App\Command;

use App\Entity\Commune;
use App\Entity\Departement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Commande d'export des zones géographiques.
 */
class AreaExportCommand extends Command
{
    /**
     * Le chemin du répertoire où déposer les fichiers d'export,
     * à partir de la racine.
     *
     * @var string
     */
    const EXPORT_PATH = 'var/export';

    // @var string
    protected static $defaultName = 'app:paradissimmo:area:export';

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
        $this->setDescription('Exporte les zones géographiques au format JSON');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $type = $this->getHelper('question')->ask($input, $output, new ChoiceQuestion(
            'Zones géographiques à exporter :',
            [
                'regions + departements',
                'communes par département',
            ]
        ));

        $io->title(sprintf('[%s] Début d\'export des zones géographiques', (new \DateTime())->format('d/m/Y H:i:s')));

        switch ($type) {
            case 'regions + departements':
                /**
                 * Les départements classés par régions.
                 *
                 * @var array
                 */
                $results = $this
                    ->em
                    ->getRepository(Departement::class)
                    ->findForExport()
                ;

                // Génération d'un fichier unique
                $filename = 'regions-departements.json';
                $filepath = sprintf(
                    '%s/%s/%s',
                    $this->projectDirectory,
                    self::EXPORT_PATH,
                    $filename
                );

                $this->fileSystem->dumpFile($filepath, json_encode(['data' => $results], JSON_UNESCAPED_UNICODE));
                $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
                break;
            case 'communes par département':
                /**
                 * Les communes classés par départements.
                 *
                 * @var array
                 */
                $results = $this
                    ->em
                    ->getRepository(Commune::class)
                    ->findForExport()
                ;

                // Génération d'un fichier par département
                foreach ($results as $result) {
                    $filename = sprintf('communes/%s.json', $result['slug']);
                    $filepath = sprintf(
                        '%s/%s/%s',
                        $this->projectDirectory,
                        self::EXPORT_PATH,
                        $filename
                    );

                    $this->fileSystem->dumpFile($filepath, json_encode(['data' => $result], JSON_UNESCAPED_UNICODE));
                    $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
                }
                break;
        }

        $io->title(sprintf('[%s] Fin d\'export des zones géographiques', (new \DateTime())->format('d/m/Y H:i:s')));

        return 0;
    }
}
