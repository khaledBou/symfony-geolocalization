<?php

namespace App\Repository;

use App\Entity\Commune;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Repository des communes.
 */
class CommuneRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commune::class);
    }

    /**
     * Récupère les coordonnées GPS (longitude, latitude)
     * des communes pour lesquelles une mise à jour des indicateurs est nécessaire.
     *
     * @todo : mutualiser avec la méthode du même nom dans QuartierRepository
     *
     * @param int      $age   Âge des indicateurs à partir duquel une mise à jour est nécessaire (en jours)
     * @param int|null $limit Nombre de communes pour lesquelles mettre à jour les indicateurs (passer null pour désactiver cette limite)
     *
     * @return array[]
     */
    public function getCoordinatesForIndicatorsToUpdate(int $age, ?int $limit): array
    {
        return $this
            ->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'SELECT DISTINCT c.id, c.centre->\'coordinates\' AS coordinates
                    FROM commune c
                    LEFT JOIN indicator i ON i.area_id = c.id
                    WHERE i.id IS NULL OR i.date < NOW() - INTERVAL \'%d days\'
                    ORDER BY c.id ASC
                    %s',
                    $age,
                    null !== $limit ? sprintf('LIMIT %d', $limit) : ''
                ),
                (new ResultSetMapping())
                    ->addScalarResult('id', 'id')
                    ->addScalarResult('coordinates', 'coordinates', 'jsonb')
            )
            ->getResult()
        ;
    }

    /**
     * Récupère les communes les plus proches des coordonnées passées en paramètre.
     *
     * @param array $coordinates Tableau de tableaux longitude, latitude
     *
     * @return array Les communes correspondantes aux coordonnées passées en paramètre, avec conservation de l'ordre
     */
    public function findNearestFromCoordinates(array $coordinates): array
    {
        $communes = [];

        if ($coordinates) {
            // Les composants de la requête SQL
            $parameters = [];

            // Construction des composants de la requête SQL
            foreach ($coordinates as $i => $coordinate) {
                $distances = [];
                foreach ($coordinates as $j => $coordinate) {
                    $lngParameterName = sprintf('lng_%s', $j);
                    $latParameterName = sprintf('lat_%s', $j);
                    $distances[] = sprintf(
                        'ST_Distance(commune.postgis_contour, ST_Point(:%s, :%s)) AS distance_%s',
                        $lngParameterName,
                        $latParameterName,
                        $j
                    );
                    $parameters[$lngParameterName] = $coordinate[0];
                    $parameters[$latParameterName] = $coordinate[1];
                }

                $sql = sprintf(
                    'SELECT area.id AS id, area.nom AS nom, area.alias AS alias, %s
                     FROM area
                     JOIN commune ON area.id = commune.id
                     ORDER BY distance_%s ASC
                     LIMIT 1',
                    implode(', ', $distances),
                    $i
                );

                $connection = $this->getEntityManager()->getConnection();
                $statement = $connection->prepare($sql);
                $statement->execute($parameters);
                $results = $statement->fetchAll();

                if (isset($results[0])) {
                    $commune = $results[0];
                    foreach (array_keys($commune) as $key) {
                        if (false !== strpos($key, 'distance_')) {
                            unset($commune[$key]);
                        }
                    }
                    $communes[] = $commune;
                }
            }
        }

        return $communes;
    }

    /**
     * Récupère les communes les plus proches d'une commune.
     *
     * @param Commune $commune La commune à partir de laquelle chercher les communes les plus proches
     * @param int     $limit   Le nombre maximal de communes à retourner
     *
     * @return string[]
     */
    public function findNearestFromCommune(Commune $commune, int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.nom, c.alias')
            ->andWhere('c != :commune')
            ->orderBy('ST_Distance(c.postgisContour, :postgis_centre)', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('commune', $commune)
            ->setParameter('postgis_centre', $commune->getPostgisCentre())
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * Récupère les communes représentant des arrondissements.
     *
     * @return array[]
     */
    public function findArrondissements()
    {
        // Les identifiants et codes postaux des communes ayant des arrondissements
        $results = $this->createQueryBuilder('c')
            ->select('c.id, c.codesPostaux')
            ->andWhere('c.arrondissements = true')
            ->getQuery()
            ->getResult()
        ;

        $arrondissements = [];

        // Construction de la condition
        foreach ($results as $i => $result) {
            $or = [];
            $parameters = [];

            foreach ($result['codesPostaux'] as $j => $codePostal) {
                $parameterName = sprintf('cp_%s_%s', $i, $j);

                $or[] = sprintf('JSONB_AG(c.codesPostaux, :%s) = TRUE', $parameterName);
                $parameters[$parameterName] = sprintf('["%s"]', $codePostal);
            }

            $arrondissements[] = [
                'commune' => $this->find($result['id']),
                'arrondissements' => $this->createQueryBuilder('c')
                    ->andWhere('c.arrondissements = FALSE')
                    ->andWhere(implode(' OR ', $or))
                    ->orderBy('c.code', 'ASC')
                    ->setParameters($parameters)
                    ->getQuery()
                    ->getResult(),
            ];
        }

        return $arrondissements;
    }

    /**
     * Récupère la liste des communes classées par départements,
     * en vue d'être exportées.
     *
     * @return array
     */
    public function findForExport(): array
    {
        $rows = $this
            ->getEntityManager()
            ->createNativeQuery(
                'SELECT LOWER(LEFT(UNACCENT(commune_area.nom), 1)) AS commune_letter,
                commune_area.id AS commune_id,
                commune_area.nom AS commune_name,
                CONCAT(commune_area.alias, \'-\', commune.codes_postaux->>0) AS commune_slug,
                departement_area.id AS departement_id,
                departement_area.nom AS departement_name,
                CONCAT(departement_area.code, \'-\', departement_area.alias) AS departement_slug,
                region_area.id AS region_id,
                region_area.nom AS region_name,
                region_area.alias AS region_slug
                FROM commune
                LEFT JOIN area commune_area ON commune_area.id = commune.id
                LEFT JOIN area departement_area ON departement_area.id = commune.departement_id
                LEFT JOIN area region_area ON region_area.id = commune.region_id
                ORDER BY departement_area.code, commune_letter ASC',
                (new ResultSetMapping())
                    ->addScalarResult('commune_letter', 'commune_letter')
                    ->addScalarResult('commune_id', 'commune_id')
                    ->addScalarResult('commune_name', 'commune_name')
                    ->addScalarResult('commune_slug', 'commune_slug')
                    ->addScalarResult('departement_id', 'departement_id')
                    ->addScalarResult('departement_name', 'departement_name')
                    ->addScalarResult('departement_slug', 'departement_slug')
                    ->addScalarResult('region_id', 'region_id')
                    ->addScalarResult('region_name', 'region_name')
                    ->addScalarResult('region_slug', 'region_slug')
            )
            ->getResult()
        ;

        $departements = [];
        $previousDepartementId = null;
        foreach ($rows as $row) {
            $departementId = $row['departement_id'];
            if ($departementId !== $previousDepartementId) {
                $departements[$departementId] = [
                    'id' => $row['departement_id'],
                    'name' => $row['departement_name'],
                    'slug' => $row['departement_slug'],
                    'region' => [
                        'id' => $row['region_id'],
                        'name' => $row['region_name'],
                        'slug' => $row['region_slug'],
                    ],
                    'communes' => [],
                ];
            }

            $letter = $row['commune_letter'];
            if (is_numeric($letter)) {
                $letter = sprintf('_%s', $letter);
            }
            if (!isset($departements[$departementId]['communes'][$letter])) {
                $departements[$departementId]['communes'][$letter] = [];
            }
            $departements[$departementId]['communes'][$letter][] = [
                'id' => $row['commune_id'],
                'name' => $row['commune_name'],
                'slug' => $row['commune_slug'],
            ];

            $previousDepartementId = $departementId;
        }

        // Reset des clés
        $departements = array_values($departements);

        return $departements;
    }
}
