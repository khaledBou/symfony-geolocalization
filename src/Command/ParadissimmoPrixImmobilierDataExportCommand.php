<?php

namespace App\Command;

use App\Entity\Commune;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Commande d'export des données pour les pages "prix immobilier" du site Paradissimmo.
 */
class ParadissimmoPrixImmobilierDataExportCommand extends Command
{
    /**
     * Le chemin du répertoire où déposer les fichiers d'export,
     * à partir de la racine.
     *
     * @var string
     */
    const EXPORT_PATH = 'var/export';

    // @var string
    protected static $defaultName = 'app:paradissimmo:prix-immobilier-data:export';

    /**
     * @var FileSystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var bool
     */
    private $forceRegeneration;

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
        $this
            ->setDescription('Exporte les données pour les pages "prix immobilier" du site Paradissimmo, au format JSON')
            ->addOption(
                'force-regeneration',
                '-f',
                InputOption::VALUE_NONE,
                'Force la regénération des fichiers s\'ils existent déjà'
            )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->forceRegeneration = $input->getOption('force-regeneration');

        $io = new SymfonyStyle($input, $output);

        $choices = $this->getHelper('question')->ask($input, $output, (new ChoiceQuestion(
            'Données à exporter (pour une sélection multiple, séparer les indices par des virgules) :',
            [
                'index',
                'régions',
                'départements',
                'communes',
                'arrondissements',
                'quartiers',
                'quartiers des arrondissements',
            ]
        ))->setMultiselect(true));

        $io->title(sprintf(
            '[%s] Début d\'export des données %s pour les pages "prix immobilier" du site Paradissimmo',
            (new \DateTime())->format('d/m/Y H:i:s'),
            implode(', ', $choices)
        ));

        if (in_array('index', $choices)) {
            $this->generateIndexFiles($io);
        }
        if (in_array('régions', $choices)) {
            $this->generateRegionFiles($io);
        }
        if (in_array('départements', $choices)) {
            $this->generateDepartementFiles($io);
        }
        if (in_array('communes', $choices)) {
            $this->generateCommuneFiles($io);
        }
        if (in_array('arrondissements', $choices)) {
            $this->generateArrondissementFiles($io);
        }
        if (in_array('quartiers', $choices)) {
            $this->generateQuartierFiles($io);
        }
        if (in_array('quartiers des arrondissements', $choices)) {
            $this->generateArrondissementQuartierFiles($io);
        }

        $io->title(sprintf(
            '[%s] Fin d\'export des données %s pour les pages "prix immobilier" du site Paradissimmo',
            (new \DateTime())->format('d/m/Y H:i:s'),
            implode(', ', $choices)
        ));

        return 0;
    }

    /**
     * Construit un fichier JSON de données pour les pages quartiers d'arrondissements "prix immobilier".
     *
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function generateArrondissementQuartierFiles(SymfonyStyle $io): void
    {
        /**
         * Les identifiants des communes représentant des arrondissements,
         * classés par commune parente.
         *
         * @var array
         */
        $arrondissements = [];

        // Requête pour construire $arrondissements
        foreach ($this
            ->em
            ->getRepository(Commune::class)
            ->findArrondissements() as $row) {
            $communeId = $row['commune']->getId();
            $arrondissements[$communeId] = [];

            foreach ($row['arrondissements'] as $arrondissement) {
                $arrondissements[$communeId][] = $arrondissement->getId();
            }
        }

        $quartiers = [];

        // Requête principale pour construire $quartiers
        $rows = $this
            ->em
            ->createNativeQuery(
                'SELECT quartier_area.id AS quartier_id, commune.id AS quartier_commune_id, quartier_area.nom AS quartier_name, departement_area.nom AS quartier_departement_name, commune_area.nom AS quartier_commune_name, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0, \'/\', quartier_area.alias) AS quartier_slug, CONCAT(departement_area.code, \'-\', departement_area.alias) AS quartier_departement_slug, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0) AS quartier_commune_slug, quartier.centre->\'coordinates\'->>0 AS quartier_lng, quartier.centre->\'coordinates\'->>1 AS quartier_lat, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value, commune.arrondissement AS quartier_in_arrondissement
                FROM indicator
                LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                LEFT JOIN area quartier_area ON quartier_area.id = indicator.area_id
                LEFT JOIN quartier ON quartier.id = quartier_area.id
                LEFT JOIN commune ON commune.id = quartier.commune_id
                LEFT JOIN area commune_area ON commune_area.id = commune.id
                LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                WHERE quartier_area.type = \'quartier\'
                AND commune.arrondissement = TRUE
                ORDER BY quartier_area.nom ASC, indicator.kel_quartier_id ASC',
                (new ResultSetMapping())
                    ->addScalarResult('quartier_id', 'id')
                    ->addScalarResult('quartier_commune_id', 'commune_id')
                    ->addScalarResult('quartier_name', 'name')
                    ->addScalarResult('quartier_departement_name', 'departement_name')
                    ->addScalarResult('quartier_commune_name', 'commune_name')
                    ->addScalarResult('quartier_slug', 'slug')
                    ->addScalarResult('quartier_departement_slug', 'departement_slug')
                    ->addScalarResult('quartier_commune_slug', 'commune_slug')
                    ->addScalarResult('quartier_lng', 'lng')
                    ->addScalarResult('quartier_lat', 'lat')
                    ->addScalarResult('quartier_in_arrondissement', 'in_arrondissement')
                    ->addScalarResult('indicator_id', 'indicator_id')
                    ->addScalarResult('indicator_value', 'indicator_value')
            )
            ->getResult()
        ;

        foreach ($rows as $row) {
            $quartierId = $row['id'];
            $indicatorId = $row['indicator_id'];
            $indicatorValue = $row['indicator_value'];

            foreach ([
                'indicator_id',
                'indicator_value',
            ] as $key) {
                unset($row[$key]);
            }
            $row['indicators'] = [];

            if (!isset($quartiers[$quartierId])) {
                $quartiers[$quartierId] = $row;
            }

            $quartiers[$quartierId]['indicators'][$indicatorId] = $indicatorValue;
        }

        // Reset des clés
        $quartiers = array_values($quartiers);

        foreach ($quartiers as $quartier) {
            $quartierId = $quartier['id'];
            $communeId = $quartier['commune_id'];

            // Les données à insérer dans le fichier
            $output = $quartier;

            // Fichier JSON à générer
            $filename = sprintf('paradissimmo/prix-immobilier/quartier-arrondissement/%s.json', $quartier['slug']);
            $filepath = sprintf(
                '%s/%s/%s',
                $this->projectDirectory,
                self::EXPORT_PATH,
                $filename
            );

            if ($this->fileSystem->exists($filepath) && !$this->forceRegeneration) {
                $io->writeln(sprintf('Fichier déjà présent : %s/%s', self::EXPORT_PATH, $filename));
                continue;
            }

            $parentCommuneId = null;
            foreach ($arrondissements as $cid => $arr) {
                if (in_array($communeId, $arr)) {
                    $parentCommuneId = $cid;
                }
            }

            // Requêtes complémentaires
            $results = [
                'quartiersOfArrondissement' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT quartier_area.id AS quartier_id, quartier_area.nom AS quartier_name, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0, \'/\', quartier_area.alias) AS quartier_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area quartier_area ON quartier_area.id = indicator.area_id
                        LEFT JOIN quartier ON quartier.id = quartier_area.id
                        LEFT JOIN commune ON commune.id = quartier.commune_id
                        LEFT JOIN area commune_area ON commune_area.id = commune.id
                        WHERE quartier_area.type = \'quartier\'
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.id = :commune_id
                        AND quartier.id != :quartier_id
                        ORDER BY quartier_area.nom ASC, quartier_area.code ASC, indicator.kel_quartier_id ASC',
                        (new ResultSetMapping())
                            ->addScalarResult('quartier_id', 'id')
                            ->addScalarResult('quartier_name', 'name')
                            ->addScalarResult('quartier_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('quartier_id', $quartierId)
                    ->setParameter('commune_id', $quartier['commune_id'])
                    ->getResult(),
                'arrondissementsOfCommune' => null !== $parentCommuneId && isset($arrondissements[$parentCommuneId]) ? $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.id IN (:arrondissements)
                        AND commune.id != :commune_id
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        ORDER BY area.code ASC, indicator.kel_quartier_id ASC',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('arrondissements', $arrondissements[$parentCommuneId])
                    ->setParameter('commune_id', $communeId)
                    ->getResult() : [],
                'nearestCommunes' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.id != :commune_id
                        ORDER BY ST_Distance(commune.postgis_centre, :quartier_point::geometry) ASC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('commune_id', $quartier['commune_id'])
                    ->setParameter('quartier_point', sprintf('POINT(%d %d)', $quartier['lng'], $quartier['lat']))
                    ->getResult(),
                'mostCommunes' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->getResult(),
            ];

            // Construction de $output
            foreach ($results as $key => $rows) {
                $output[$key] = [];

                foreach ($rows as $row) {
                    $id = $row['id'];
                    if (!isset($output[$key][$id])) {
                        $output[$key][$id] = [
                            'id' => $id,
                            'name' => $row['name'],
                            'slug' => $row['slug'],
                            'prix_moyen' => [
                                'appart' => null,
                                'maison' => null,
                            ],
                        ];
                    }

                    $indicatorId = $row['indicator_id'];
                    $k = [
                        'prix_appart_moyen' => 'appart',
                        'prix_maison_moyen' => 'maison',
                    ][$indicatorId];
                    $output[$key][$id]['prix_moyen'][$k] = $row['indicator_value'];
                }

                // Reset des clés
                $output[$key] = array_values($output[$key]);
            }

            $this->fileSystem->dumpFile($filepath, json_encode(['data' => $output], JSON_UNESCAPED_UNICODE));
            $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
        }
    }

    /**
     * Construit un fichier JSON de données pour les pages quartiers "prix immobilier".
     *
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function generateQuartierFiles(SymfonyStyle $io): void
    {
        $quartiers = [];

        // Requête principale pour construire $quartiers
        $rows = $this
            ->em
            ->createNativeQuery(
                'SELECT quartier_area.id AS quartier_id, departement_area.id AS quartier_departement_id, quartier_area.nom AS quartier_name, departement_area.nom AS quartier_departement_name, commune_area.nom AS quartier_commune_name, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0, \'/\', quartier_area.alias) AS quartier_slug, CONCAT(departement_area.code, \'-\', departement_area.alias) AS quartier_departement_slug,  CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0) AS quartier_commune_slug, quartier.centre->\'coordinates\'->>0 AS quartier_lng, quartier.centre->\'coordinates\'->>1 AS quartier_lat, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value, commune.arrondissement AS quartier_in_arrondissement
                FROM indicator
                LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                LEFT JOIN area quartier_area ON quartier_area.id = indicator.area_id
                LEFT JOIN quartier ON quartier.id = quartier_area.id
                LEFT JOIN commune ON commune.id = quartier.commune_id
                LEFT JOIN area commune_area ON commune_area.id = commune.id
                LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                WHERE quartier_area.type = \'quartier\'
                ORDER BY quartier_area.nom ASC, indicator.kel_quartier_id ASC',
                (new ResultSetMapping())
                    ->addScalarResult('quartier_id', 'id')
                    ->addScalarResult('quartier_name', 'name')
                    ->addScalarResult('quartier_departement_id', 'departement_id')
                    ->addScalarResult('quartier_departement_name', 'departement_name')
                    ->addScalarResult('quartier_commune_name', 'commune_name')
                    ->addScalarResult('quartier_slug', 'slug')
                    ->addScalarResult('quartier_commune_slug', 'commune_slug')
                    ->addScalarResult('quartier_departement_slug', 'departement_slug')
                    ->addScalarResult('quartier_lng', 'lng')
                    ->addScalarResult('quartier_lat', 'lat')
                    ->addScalarResult('quartier_in_arrondissement', 'in_arrondissement')
                    ->addScalarResult('indicator_id', 'indicator_id')
                    ->addScalarResult('indicator_value', 'indicator_value')
            )
            ->getResult()
        ;

        foreach ($rows as $row) {
            $quartierId = $row['id'];
            $indicatorId = $row['indicator_id'];
            $indicatorValue = $row['indicator_value'];

            foreach ([
                'indicator_id',
                'indicator_value',
            ] as $key) {
                unset($row[$key]);
            }
            $row['indicators'] = [];

            if (!isset($quartiers[$quartierId])) {
                $quartiers[$quartierId] = $row;
            }

            $quartiers[$quartierId]['indicators'][$indicatorId] = $indicatorValue;
        }

        // Reset des clés
        $quartiers = array_values($quartiers);

        foreach ($quartiers as $quartier) {
            $quartierId = $quartier['id'];

            // Les données à insérer dans le fichier
            $output = $quartier;

            // Fichier JSON à générer
            $filename = sprintf('paradissimmo/prix-immobilier/quartier/%s.json', $quartier['slug']);
            $filepath = sprintf(
                '%s/%s/%s',
                $this->projectDirectory,
                self::EXPORT_PATH,
                $filename
            );

            if ($this->fileSystem->exists($filepath) && !$this->forceRegeneration) {
                $io->writeln(sprintf('Fichier déjà présent : %s/%s', self::EXPORT_PATH, $filename));
                continue;
            }

            // Requêtes complémentaires
            $results = [
                'nearestQuartiers' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT quartier_area.id AS quartier_id, quartier_area.nom AS quartier_name, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0, \'/\', quartier_area.alias) AS quartier_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area quartier_area ON quartier_area.id = indicator.area_id
                        LEFT JOIN quartier ON quartier.id = quartier_area.id
                        LEFT JOIN commune ON commune.id = quartier.commune_id
                        LEFT JOIN area commune_area ON commune_area.id = commune.id
                        WHERE quartier_area.type = \'quartier\'
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND quartier.id != :quartier_id
                        ORDER BY ST_Distance(quartier.postgis_centre, :quartier_point::geometry) ASC, quartier_area.nom ASC, quartier_area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 20',
                        (new ResultSetMapping())
                            ->addScalarResult('quartier_id', 'id')
                            ->addScalarResult('quartier_name', 'name')
                            ->addScalarResult('quartier_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('quartier_id', $quartierId)
                    ->setParameter('quartier_point', sprintf('POINT(%d %d)', $quartier['lng'], $quartier['lat']))
                    ->getResult(),
                'mostCommunesOfDepartment' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.departement_id = :departement_id
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 20',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('departement_id', $quartier['departement_id'])
                    ->getResult(),
                'mostCommunes' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->getResult(),
            ];

            // Construction de $output
            foreach ($results as $key => $rows) {
                $output[$key] = [];

                foreach ($rows as $row) {
                    $id = $row['id'];
                    if (!isset($output[$key][$id])) {
                        $output[$key][$id] = [
                            'id' => $id,
                            'name' => $row['name'],
                            'slug' => $row['slug'],
                            'prix_moyen' => [
                                'appart' => null,
                                'maison' => null,
                            ],
                        ];
                    }

                    $indicatorId = $row['indicator_id'];
                    $k = [
                        'prix_appart_moyen' => 'appart',
                        'prix_maison_moyen' => 'maison',
                    ][$indicatorId];
                    $output[$key][$id]['prix_moyen'][$k] = $row['indicator_value'];
                }

                // Reset des clés
                $output[$key] = array_values($output[$key]);
            }

            $this->fileSystem->dumpFile($filepath, json_encode(['data' => $output], JSON_UNESCAPED_UNICODE));
            $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
        }
    }

    /**
     * Construit un fichier JSON de données pour les pages arrondissements "prix immobilier".
     *
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function generateArrondissementFiles(SymfonyStyle $io): void
    {
        /**
         * Les identifiants des communes représentant des arrondissements,
         * classés par commune parente.
         *
         * @var array
         */
        $arrondissementsArray = [];

        // Requête pour construire $arrondissementsArray
        foreach ($this
            ->em
            ->getRepository(Commune::class)
            ->findArrondissements() as $row) {
            $communeId = $row['commune']->getId();
            $arrondissementsArray[$communeId] = [];

            foreach ($row['arrondissements'] as $arrondissement) {
                $arrondissementsArray[$communeId][] = $arrondissement;
            }
        }

        foreach ($arrondissementsArray as $communeId => $arrondissements) {
            $arrondissementsIds = [];
            foreach ($arrondissements as $arrondissement) {
                $arrondissementsIds[] = $arrondissement->getId();
            }

            foreach ($arrondissements as $arrondissement) {
                $arrondissementId = $arrondissement->getId();

                // Requête principale
                $rows = $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS arrondissement_id, area.nom AS arrondissement_name, departement_area.nom AS arrondissement_departement_name, CONCAT(departement_area.code, \'-\', departement_area.alias) AS arrondissement_departement_slug, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS arrondissement_slug, commune.centre->\'coordinates\'->>0 AS arrondissement_lng, commune.centre->\'coordinates\'->>1 AS arrondissement_lat, commune.arrondissement AS arrondissement_is_arrondissement, commune.codes_postaux AS arrondissement_codes_postaux, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                        WHERE area.type = \'commune\'
                        AND commune.id IN (:arrondissement_id)
                        ORDER BY area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC',
                        (new ResultSetMapping())
                            ->addScalarResult('arrondissement_id', 'id')
                            ->addScalarResult('arrondissement_name', 'name')
                            ->addScalarResult('arrondissement_departement_name', 'departement_name')
                            ->addScalarResult('arrondissement_slug', 'slug')
                            ->addScalarResult('arrondissement_departement_slug', 'departement_slug')
                            ->addScalarResult('arrondissement_lng', 'lng')
                            ->addScalarResult('arrondissement_lat', 'lat')
                            ->addScalarResult('arrondissement_is_arrondissement', 'is_arrondissement')
                            ->addScalarResult('arrondissement_codes_postaux', 'codes_postaux')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('arrondissement_id', $arrondissementId)
                    ->getResult()
                ;

                $output = [];

                foreach ($rows as $row) {
                    $indicatorId = $row['indicator_id'];
                    $indicatorValue = $row['indicator_value'];

                    foreach ([
                        'indicator_id',
                        'indicator_value',
                    ] as $key) {
                        unset($row[$key]);
                    }

                    $row['codes_postaux'] = json_decode($row['codes_postaux']);

                    if (empty($output)) {
                        $row['indicators'] = [];
                        $output = $row;
                    }

                    $output['indicators'][$indicatorId] = $indicatorValue;
                }

                // Fichier JSON à générer
                $filename = sprintf('paradissimmo/prix-immobilier/arrondissement/%s.json', $output['slug']);
                $filepath = sprintf(
                    '%s/%s/%s',
                    $this->projectDirectory,
                    self::EXPORT_PATH,
                    $filename
                );

                if ($this->fileSystem->exists($filepath) && !$this->forceRegeneration) {
                    $io->writeln(sprintf('Fichier déjà présent : %s/%s', self::EXPORT_PATH, $filename));
                    continue;
                }

                // Requêtes complémentaires
                $results = [
                    'arrondissements' => $this
                        ->em
                        ->createNativeQuery(
                            'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                            FROM indicator
                            LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                            LEFT JOIN area ON area.id = indicator.area_id
                            LEFT JOIN commune ON commune.id = area.id
                            WHERE area.type = \'commune\'
                            AND commune.id IN (:arrondissements)
                            AND commune.id != :arrondissement_id
                            AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                            ORDER BY area.code ASC, indicator.kel_quartier_id ASC
                            LIMIT 40',
                            (new ResultSetMapping())
                                ->addScalarResult('commune_id', 'id')
                                ->addScalarResult('commune_name', 'name')
                                ->addScalarResult('commune_slug', 'slug')
                                ->addScalarResult('indicator_id', 'indicator_id')
                                ->addScalarResult('indicator_value', 'indicator_value')
                        )
                        ->setParameter('arrondissements', $arrondissementsIds)
                        ->setParameter('arrondissement_id', $arrondissementId)
                        ->getResult(),
                    'quartiersOfArrondissement' => $this
                        ->em
                        ->createNativeQuery(
                            'SELECT quartier_area.id AS quartier_id, quartier_area.nom AS quartier_name, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0, \'/\', quartier_area.alias) AS quartier_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                            FROM indicator
                            LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                            LEFT JOIN area quartier_area ON quartier_area.id = indicator.area_id
                            LEFT JOIN quartier ON quartier.id = quartier_area.id
                            LEFT JOIN commune ON commune.id = quartier.commune_id
                            LEFT JOIN area commune_area ON commune_area.id = commune.id
                            WHERE quartier_area.type = \'quartier\'
                            AND quartier.commune_id = :commune_id
                            AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                            ORDER BY quartier_area.nom ASC, indicator.kel_quartier_id ASC
                            LIMIT 40',
                            (new ResultSetMapping())
                                ->addScalarResult('quartier_id', 'id')
                                ->addScalarResult('quartier_name', 'name')
                                ->addScalarResult('quartier_slug', 'slug')
                                ->addScalarResult('indicator_id', 'indicator_id')
                                ->addScalarResult('indicator_value', 'indicator_value')
                        )
                        ->setParameter('commune_id', $arrondissementId)
                        ->getResult(),
                    'nearestCommunes' => $this
                        ->em
                        ->createNativeQuery(
                            'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                            FROM indicator
                            LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                            LEFT JOIN area ON area.id = indicator.area_id
                            LEFT JOIN commune ON commune.id = area.id
                            WHERE area.type = \'commune\'
                            AND commune.population IS NOT NULL
                            AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                            AND commune.id != :commune_id
                            ORDER BY ST_Distance(commune.postgis_centre, :commune_point::geometry) ASC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                            LIMIT 40',
                            (new ResultSetMapping())
                                ->addScalarResult('commune_id', 'id')
                                ->addScalarResult('commune_name', 'name')
                                ->addScalarResult('commune_slug', 'slug')
                                ->addScalarResult('indicator_id', 'indicator_id')
                                ->addScalarResult('indicator_value', 'indicator_value')
                        )
                        ->setParameter('commune_id', $communeId)
                        ->setParameter('commune_point', sprintf(
                            'POINT(%d %d)',
                            $arrondissement->getCentre()['coordinates'][0],
                            $arrondissement->getCentre()['coordinates'][1]
                        ))
                        ->getResult(),
                    'mostCommunes' => $this
                        ->em
                        ->createNativeQuery(
                            'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                            FROM indicator
                            LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                            LEFT JOIN area ON area.id = indicator.area_id
                            LEFT JOIN commune ON commune.id = area.id
                            WHERE area.type = \'commune\'
                            AND commune.population IS NOT NULL
                            AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                            ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                            LIMIT 40',
                            (new ResultSetMapping())
                                ->addScalarResult('commune_id', 'id')
                                ->addScalarResult('commune_name', 'name')
                                ->addScalarResult('commune_slug', 'slug')
                                ->addScalarResult('indicator_id', 'indicator_id')
                                ->addScalarResult('indicator_value', 'indicator_value')
                        )
                        ->getResult(),
                ];

                // Construction de $output
                foreach ($results as $key => $rows) {
                    $output[$key] = [];

                    foreach ($rows as $row) {
                        $id = $row['id'];
                        if (!isset($output[$key][$id])) {
                            $output[$key][$id] = [
                                'id' => $id,
                                'name' => $row['name'],
                                'slug' => $row['slug'],
                                'prix_moyen' => [
                                    'appart' => null,
                                    'maison' => null,
                                ],
                            ];
                        }

                        $indicatorId = $row['indicator_id'];
                        $k = [
                            'prix_appart_moyen' => 'appart',
                            'prix_maison_moyen' => 'maison',
                        ][$indicatorId];
                        $output[$key][$id]['prix_moyen'][$k] = $row['indicator_value'];
                    }

                    // Reset des clés
                    $output[$key] = array_values($output[$key]);
                }

                $this->fileSystem->dumpFile($filepath, json_encode(['data' => $output], JSON_UNESCAPED_UNICODE));
                $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
            }
        }
    }

    /**
     * Construit des fichiers JSON de données pour les pages communes "prix immobilier".
     *
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function generateCommuneFiles(SymfonyStyle $io): void
    {
        /**
         * Les identifiants des communes représentant des arrondissements,
         * classés par commune parente.
         *
         * @var array
         */
        $arrondissements = [];

        // Requête pour construire $arrondissements
        foreach ($this
            ->em
            ->getRepository(Commune::class)
            ->findArrondissements() as $row) {
            $communeId = $row['commune']->getId();
            $arrondissements[$communeId] = [];

            foreach ($row['arrondissements'] as $arrondissement) {
                $arrondissements[$communeId][] = $arrondissement->getId();
            }
        }

        $communes = [];

        // Requête principale pour construire $communes
        $rows = $this
            ->em
            ->createNativeQuery(
                'SELECT commune_area.id AS commune_id, departement_area.id AS commune_departement_id, commune_area.nom AS commune_name, departement_area.nom AS commune_departement_name, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, CONCAT(departement_area.code, \'-\', departement_area.alias) AS commune_departement_slug, commune.centre->\'coordinates\'->>0 AS commune_lng, commune.centre->\'coordinates\'->>1 AS commune_lat, commune.arrondissement AS commune_is_arrondissement, commune.codes_postaux AS commune_codes_postaux, indicator.kel_quartier_id AS indicator_id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS indicator_value
                FROM indicator
                LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                LEFT JOIN commune ON commune.id = commune_area.id
                LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                WHERE commune_area.type=\'commune\'
                GROUP BY commune_area.id, departement_area.id, departement_area.nom, departement_area.code, departement_area.alias, commune_area.nom, commune.codes_postaux, commune.centre, commune.arrondissement, indicator.kel_quartier_id
                ORDER BY commune_area.nom ASC, commune_area.code ASC, indicator.kel_quartier_id ASC',
                (new ResultSetMapping())
                    ->addScalarResult('commune_id', 'id')
                    ->addScalarResult('commune_departement_id', 'departement_id')
                    ->addScalarResult('commune_name', 'name')
                    ->addScalarResult('commune_departement_name', 'departement_name')
                    ->addScalarResult('commune_slug', 'slug')
                    ->addScalarResult('commune_departement_slug', 'departement_slug')
                    ->addScalarResult('commune_lng', 'lng')
                    ->addScalarResult('commune_lat', 'lat')
                    ->addScalarResult('commune_is_arrondissement', 'is_arrondissement')
                    ->addScalarResult('commune_codes_postaux', 'codes_postaux')
                    ->addScalarResult('indicator_id', 'indicator_id')
                    ->addScalarResult('indicator_value', 'indicator_value')
            )
            ->getResult()
        ;

        foreach ($rows as $row) {
            $communeId = $row['id'];
            $indicatorId = $row['indicator_id'];
            $indicatorValue = $row['indicator_value'];

            foreach ([
                'indicator_id',
                'indicator_value',
            ] as $key) {
                unset($row[$key]);
            }
            $row['indicators'] = [];
            $row['codes_postaux'] = json_decode($row['codes_postaux']);

            if (!isset($communes[$communeId])) {
                $communes[$communeId] = $row;
            }

            $communes[$communeId]['indicators'][$indicatorId] = $indicatorValue;
        }

        // Reset des clés
        $communes = array_values($communes);

        foreach ($communes as $commune) {
            $communeId = $commune['id'];

            // Les données à insérer dans le fichier
            $output = $commune;

            // Fichier JSON à générer
            $filename = sprintf('paradissimmo/prix-immobilier/commune/%s.json', $commune['slug']);
            $filepath = sprintf(
                '%s/%s/%s',
                $this->projectDirectory,
                self::EXPORT_PATH,
                $filename
            );

            if ($this->fileSystem->exists($filepath) && !$this->forceRegeneration) {
                $io->writeln(sprintf('Fichier déjà présent : %s/%s', self::EXPORT_PATH, $filename));
                continue;
            }

            // Requêtes complémentaires
            $results = [
                'arrondissements' => isset($arrondissements[$communeId]) ? $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.id IN (:arrondissements)
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        ORDER BY area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('arrondissements', $arrondissements[$communeId])
                    ->getResult() : [],
                'quartiersOfCommune' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT quartier_area.id AS quartier_id, quartier_area.nom AS quartier_name, CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0, \'/\', quartier_area.alias) AS quartier_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area quartier_area ON quartier_area.id = indicator.area_id
                        LEFT JOIN quartier ON quartier.id = quartier_area.id
                        LEFT JOIN commune ON commune.id = quartier.commune_id
                        LEFT JOIN area commune_area ON commune_area.id = commune.id
                        WHERE quartier_area.type = \'quartier\'
                        AND quartier.commune_id = :commune_id
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        ORDER BY quartier_area.nom ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('quartier_id', 'id')
                            ->addScalarResult('quartier_name', 'name')
                            ->addScalarResult('quartier_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('commune_id', $communeId)
                    ->getResult(),
                'nearestCommunes' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.id != :commune_id
                        ORDER BY ST_Distance(commune.postgis_centre, :commune_point::geometry) ASC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 20',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('commune_id', $communeId)
                    ->setParameter('commune_point', sprintf('POINT(%d %d)', $commune['lng'], $commune['lat']))
                    ->getResult(),
                'mostCommunesOfDepartment' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.departement_id = :departement_id
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 20',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('departement_id', $commune['departement_id'])
                    ->getResult(),
                'mostCommunes' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->getResult(),
            ];

            // Construction de $output
            foreach ($results as $key => $rows) {
                $output[$key] = [];

                foreach ($rows as $row) {
                    $id = $row['id'];
                    if (!isset($output[$key][$id])) {
                        $output[$key][$id] = [
                            'id' => $id,
                            'name' => $row['name'],
                            'slug' => $row['slug'],
                            'prix_moyen' => [
                                'appart' => null,
                                'maison' => null,
                            ],
                        ];
                    }

                    $indicatorId = $row['indicator_id'];
                    $k = [
                        'prix_appart_moyen' => 'appart',
                        'prix_maison_moyen' => 'maison',
                    ][$indicatorId];
                    $output[$key][$id]['prix_moyen'][$k] = $row['indicator_value'];
                }

                // Reset des clés
                $output[$key] = array_values($output[$key]);
            }

            $this->fileSystem->dumpFile($filepath, json_encode(['data' => $output], JSON_UNESCAPED_UNICODE));
            $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
        }
    }

    /**
     * Construit des fichiers JSON de données pour les pages départements "prix immobilier".
     *
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function generateDepartementFiles(SymfonyStyle $io): void
    {
        // Requête principale
        $departements = $this
            ->em
            ->createNativeQuery(
                'SELECT area.id AS departement_id, departement.region_id AS departement_region_id, area.nom AS departement_name, CONCAT(area.code, \'-\', area.alias) AS departement_slug, departement.centre->\'coordinates\'->>0 AS departement_lng, departement.centre->\'coordinates\'->>1 AS departement_lat
                FROM departement
                LEFT join area ON area.id = departement.id
                ORDER BY area.code ASC',
                (new ResultSetMapping())
                    ->addScalarResult('departement_id', 'id')
                    ->addScalarResult('departement_region_id', 'region_id')
                    ->addScalarResult('departement_name', 'name')
                    ->addScalarResult('departement_slug', 'slug')
                    ->addScalarResult('departement_lng', 'lng')
                    ->addScalarResult('departement_lat', 'lat')
            )
            ->getResult()
        ;

        foreach ($departements as $departement) {
            // Les données à insérer dans le fichier
            $output = $departement;

            // Fichier JSON à générer
            $filename = sprintf('paradissimmo/prix-immobilier/departement/%s.json', $departement['slug']);
            $filepath = sprintf(
                '%s/%s/%s',
                $this->projectDirectory,
                self::EXPORT_PATH,
                $filename
            );

            if ($this->fileSystem->exists($filepath) && !$this->forceRegeneration) {
                $io->writeln(sprintf('Fichier déjà présent : %s/%s', self::EXPORT_PATH, $filename));
                continue;
            }

            // Indicateurs
            $indicators = $this
                ->em
                ->createNativeQuery(
                    'SELECT indicator.kel_quartier_id AS id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS value
                    FROM indicator
                    LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                    LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                    LEFT JOIN commune ON commune.id = commune_area.id
                    WHERE commune_area.type=\'commune\'
                    AND commune.departement_id = :departement_id
                    GROUP BY indicator.kel_quartier_id
                    ORDER BY indicator.kel_quartier_id ASC',
                    (new ResultSetMapping())
                        ->addScalarResult('id', 'id')
                        ->addScalarResult('value', 'value')
                )
                ->setParameter('departement_id', $departement['id'])
                ->getResult()
            ;

            // Construction de $output
            $output['indicators'] = [];
            foreach ($indicators as $indicator) {
                $indicatorId = $indicator['id'];
                $output['indicators'][$indicatorId] = $indicator['value'];
            }

            // Requêtes complémentaires
            $results = [
                'mostCommunes' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->getResult(),
                'mostCommunesOfDepartment' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.departement_id = :departement_id
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 40',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('departement_id', $departement['id'])
                    ->getResult(),
                'departementsOfRegion' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT departement_area.id AS departement_id, departement_area.nom AS departement_name, CONCAT(departement_area.code, \'-\', departement_area.alias) AS departement_slug, indicator.kel_quartier_id AS indicator_id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = commune_area.id
                        LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                        WHERE commune_area.type=\'commune\'
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.region_id = :region_id
                        GROUP BY departement_area.id, departement_area.nom, departement_area.alias, indicator.kel_quartier_id
                        ORDER BY departement_area.code ASC, indicator.kel_quartier_id ASC',
                        (new ResultSetMapping())
                            ->addScalarResult('departement_id', 'id')
                            ->addScalarResult('departement_name', 'name')
                            ->addScalarResult('departement_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('region_id', $departement['region_id'])
                    ->getResult(),
            ];

            // Construction de $output
            foreach ($results as $key => $rows) {
                $output[$key] = [];

                foreach ($rows as $row) {
                    $id = $row['id'];
                    if (!isset($output[$key][$id])) {
                        $output[$key][$id] = [
                            'id' => $id,
                            'name' => $row['name'],
                            'slug' => $row['slug'],
                            'prix_moyen' => [
                                'appart' => null,
                                'maison' => null,
                            ],
                        ];
                    }

                    $indicatorId = $row['indicator_id'];
                    $k = [
                        'prix_appart_moyen' => 'appart',
                        'prix_maison_moyen' => 'maison',
                    ][$indicatorId];
                    $output[$key][$id]['prix_moyen'][$k] = $row['indicator_value'];
                }

                // Reset des clés
                $output[$key] = array_values($output[$key]);
            }

            $this->fileSystem->dumpFile($filepath, json_encode(['data' => $output], JSON_UNESCAPED_UNICODE));
            $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
        }
    }

    /**
     * Construit des fichiers JSON de données pour les pages régions "prix immobilier".
     *
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function generateRegionFiles(SymfonyStyle $io): void
    {
        // Requête principale
        $regions = $this
            ->em
            ->createNativeQuery(
                'SELECT area.id AS region_id, area.nom AS region_name, area.alias AS region_slug, region.centre->\'coordinates\'->>0 AS region_lng, region.centre->\'coordinates\'->>1 AS region_lat
                FROM region
                LEFT join area ON area.id = region.id
                ORDER BY area.nom ASC',
                (new ResultSetMapping())
                    ->addScalarResult('region_id', 'id')
                    ->addScalarResult('region_name', 'name')
                    ->addScalarResult('region_slug', 'slug')
                    ->addScalarResult('region_lng', 'lng')
                    ->addScalarResult('region_lat', 'lat')
            )
            ->getResult()
        ;

        foreach ($regions as $region) {
            // Les données à insérer dans le fichier
            $output = $region;

            // Fichier JSON à générer
            $filename = sprintf('paradissimmo/prix-immobilier/region/%s.json', $region['slug']);
            $filepath = sprintf(
                '%s/%s/%s',
                $this->projectDirectory,
                self::EXPORT_PATH,
                $filename
            );

            if ($this->fileSystem->exists($filepath) && !$this->forceRegeneration) {
                $io->writeln(sprintf('Fichier déjà présent : %s/%s', self::EXPORT_PATH, $filename));
                continue;
            }

            // Indicateurs
            $indicators = $this
                ->em
                ->createNativeQuery(
                    'SELECT indicator.kel_quartier_id AS id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS value
                    FROM indicator
                    LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                    LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                    LEFT JOIN commune ON commune.id = commune_area.id
                    WHERE commune_area.type=\'commune\'
                    AND commune.region_id = :region_id
                    GROUP BY indicator.kel_quartier_id
                    ORDER BY indicator.kel_quartier_id ASC',
                    (new ResultSetMapping())
                        ->addScalarResult('id', 'id')
                        ->addScalarResult('value', 'value')
                )
                ->setParameter('region_id', $region['id'])
                ->getResult()
            ;

            // Construction de $output
            $output['indicators'] = [];
            foreach ($indicators as $indicator) {
                $indicatorId = $indicator['id'];
                $output['indicators'][$indicatorId] = $indicator['value'];
            }

            // Requêtes complémentaires
            $results = [
                'mostCommunes' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area ON area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = area.id
                        WHERE area.type = \'commune\'
                        AND commune.population IS NOT NULL
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.region_id = :region_id
                        ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                        LIMIT 48',
                        (new ResultSetMapping())
                            ->addScalarResult('commune_id', 'id')
                            ->addScalarResult('commune_name', 'name')
                            ->addScalarResult('commune_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('region_id', $region['id'])
                    ->getResult(),
                'departements' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT departement_area.id AS departement_id, departement_area.nom AS departement_name, CONCAT(departement_area.code, \'-\', departement_area.alias) AS departement_slug, indicator.kel_quartier_id AS indicator_id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = commune_area.id
                        LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                        WHERE commune_area.type=\'commune\'
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND commune.region_id = :region_id
                        GROUP BY departement_area.id, departement_area.nom, departement_area.alias, indicator.kel_quartier_id
                        ORDER BY departement_area.code ASC, indicator.kel_quartier_id ASC',
                        (new ResultSetMapping())
                            ->addScalarResult('departement_id', 'id')
                            ->addScalarResult('departement_name', 'name')
                            ->addScalarResult('departement_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameter('region_id', $region['id'])
                    ->getResult(),
                'nearestRegions' => $this
                    ->em
                    ->createNativeQuery(
                        'SELECT region_area.id AS region_id, region_area.nom AS region_name, region_area.alias AS region_slug, indicator.kel_quartier_id AS indicator_id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS indicator_value
                        FROM indicator
                        LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                        LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                        LEFT JOIN commune ON commune.id = commune_area.id
                        LEFT JOIN area region_area ON region_area.id = commune.region_id
                        LEFT JOIN region ON region.id = region_area.id
                        WHERE commune_area.type=\'commune\'
                        AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                        AND region_area.id != :region_id
                        GROUP BY region_area.id, region_area.nom, region_area.alias, region.postgis_centre, indicator.kel_quartier_id
                        ORDER BY ST_Distance(region.postgis_centre, :region_point::geometry) ASC, region_area.nom ASC, indicator.kel_quartier_id ASC
                        LIMIT 10',
                        (new ResultSetMapping())
                            ->addScalarResult('region_id', 'id')
                            ->addScalarResult('region_name', 'name')
                            ->addScalarResult('region_slug', 'slug')
                            ->addScalarResult('indicator_id', 'indicator_id')
                            ->addScalarResult('indicator_value', 'indicator_value')
                    )
                    ->setParameters([
                        'region_id' => $region['id'],
                        'region_point' => sprintf('POINT(%d %d)', $region['lng'], $region['lat']),
                    ])
                    ->getResult(),
            ];

            // Construction de $output
            foreach ($results as $key => $rows) {
                $output[$key] = [];

                foreach ($rows as $row) {
                    $id = $row['id'];
                    if (!isset($output[$key][$id])) {
                        $output[$key][$id] = [
                            'id' => $id,
                            'name' => $row['name'],
                            'slug' => $row['slug'],
                            'prix_moyen' => [
                                'appart' => null,
                                'maison' => null,
                            ],
                        ];
                    }

                    $indicatorId = $row['indicator_id'];
                    $k = [
                        'prix_appart_moyen' => 'appart',
                        'prix_maison_moyen' => 'maison',
                    ][$indicatorId];
                    $output[$key][$id]['prix_moyen'][$k] = $row['indicator_value'];
                }

                // Reset des clés
                $output[$key] = array_values($output[$key]);
            }

            $this->fileSystem->dumpFile($filepath, json_encode(['data' => $output], JSON_UNESCAPED_UNICODE));
            $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
        }
    }

    /**
     * Construit un fichier JSON de données pour la page d'accueil "prix immobilier".
     *
     * @param SymfonyStyle $io
     *
     * @return void
     */
    private function generateIndexFiles(SymfonyStyle $io): void
    {
        // Fichier JSON à générer
        $filename = 'paradissimmo/prix-immobilier/index.json';
        $filepath = sprintf(
            '%s/%s/%s',
            $this->projectDirectory,
            self::EXPORT_PATH,
            $filename
        );

        if ($this->fileSystem->exists($filepath) && !$this->forceRegeneration) {
            $io->writeln(sprintf('Fichier déjà présent : %s/%s', self::EXPORT_PATH, $filename));

            return;
        }

        // Requêtes
        $results = [
            'mostCommunes' => $this
                ->em
                ->createNativeQuery(
                    'SELECT area.id AS commune_id, area.nom AS commune_name, CONCAT(area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug, indicator.kel_quartier_id AS indicator_id, int_indicator.value AS indicator_value
                    FROM indicator
                    LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                    LEFT JOIN area ON area.id = indicator.area_id
                    LEFT JOIN commune ON commune.id = area.id
                    WHERE area.type = \'commune\'
                    AND commune.population IS NOT NULL
                    AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                    ORDER BY commune.population DESC, area.nom ASC, area.code ASC, indicator.kel_quartier_id ASC
                    LIMIT 40',
                    (new ResultSetMapping())
                        ->addScalarResult('commune_id', 'id')
                        ->addScalarResult('commune_name', 'name')
                        ->addScalarResult('commune_slug', 'slug')
                        ->addScalarResult('indicator_id', 'indicator_id')
                        ->addScalarResult('indicator_value', 'indicator_value')
                )
                ->getResult(),
            'regions' => $this
                ->em
                ->createNativeQuery(
                    'SELECT region_area.id AS region_id, region_area.nom AS region_name, region_area.alias AS region_slug, indicator.kel_quartier_id AS indicator_id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS indicator_value
                    FROM indicator
                    LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                    LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                    LEFT JOIN commune ON commune.id = commune_area.id
                    LEFT JOIN area region_area ON region_area.id = commune.region_id
                    WHERE commune_area.type=\'commune\'
                    AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                    GROUP BY region_area.id, region_area.nom, region_area.alias, indicator.kel_quartier_id
                    ORDER BY region_area.nom ASC, indicator.kel_quartier_id ASC',
                    (new ResultSetMapping())
                        ->addScalarResult('region_id', 'id')
                        ->addScalarResult('region_name', 'name')
                        ->addScalarResult('region_slug', 'slug')
                        ->addScalarResult('indicator_id', 'indicator_id')
                        ->addScalarResult('indicator_value', 'indicator_value')
                )
                ->getResult(),
            'departements' => $this
                ->em
                ->createNativeQuery(
                    'SELECT departement_area.id AS departement_id, departement_area.nom AS departement_name, CONCAT(departement_area.code, \'-\', departement_area.alias) AS departement_slug, indicator.kel_quartier_id AS indicator_id, CAST(ROUND(AVG(int_indicator.value)) AS INT) AS indicator_value
                    FROM indicator
                    LEFT JOIN int_indicator ON int_indicator.id = indicator.id
                    LEFT JOIN area commune_area ON commune_area.id = indicator.area_id
                    LEFT JOIN commune ON commune.id = commune_area.id
                    LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                    WHERE commune_area.type=\'commune\'
                    AND indicator.kel_quartier_id IN (\'prix_appart_moyen\', \'prix_maison_moyen\')
                    GROUP BY departement_area.id, departement_area.nom, departement_area.alias, indicator.kel_quartier_id
                    ORDER BY departement_area.code ASC, indicator.kel_quartier_id ASC',
                    (new ResultSetMapping())
                        ->addScalarResult('departement_id', 'id')
                        ->addScalarResult('departement_name', 'name')
                        ->addScalarResult('departement_slug', 'slug')
                        ->addScalarResult('indicator_id', 'indicator_id')
                        ->addScalarResult('indicator_value', 'indicator_value')
                )
                ->getResult(),
        ];

        // Les données à insérer dans le fichier
        $output = [];

        // Construction de $output
        foreach ($results as $key => $rows) {
            $output[$key] = [];

            foreach ($rows as $row) {
                $id = $row['id'];
                if (!isset($output[$key][$id])) {
                    $output[$key][$id] = [
                        'id' => $id,
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'prix_moyen' => [
                            'appart' => null,
                            'maison' => null,
                        ],
                    ];
                }

                $indicatorId = $row['indicator_id'];
                $k = [
                    'prix_appart_moyen' => 'appart',
                    'prix_maison_moyen' => 'maison',
                ][$indicatorId];
                $output[$key][$id]['prix_moyen'][$k] = $row['indicator_value'];
            }

            // Reset des clés
            $output[$key] = array_values($output[$key]);
        }

        $this->fileSystem->dumpFile($filepath, json_encode(['data' => $output], JSON_UNESCAPED_UNICODE));
        $io->writeln(sprintf('Fichier généré : %s/%s', self::EXPORT_PATH, $filename));
    }
}
