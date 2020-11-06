<?php

namespace App\Repository;

use App\Entity\Departement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Repository des départements.
 */
class DepartementRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Departement::class);
    }

    /**
     * Récupère la liste des départements classés par régions,
     * en vue d'être exportés.
     *
     * @return array
     */
    public function findForExport(): array
    {
        $rows = $this
            ->getEntityManager()
            ->createNativeQuery(
                'SELECT departement_area.id AS departement_id,
                departement_area.nom AS departement_name,
                CONCAT(departement_area.code, \'-\', departement_area.alias) AS departement_slug,
                region_area.id AS region_id,
                region_area.nom AS region_name,
                region_area.alias AS region_slug
                FROM departement
                LEFT JOIN area departement_area ON departement_area.id = departement.id
                LEFT JOIN area region_area ON region_area.id = departement.region_id
                ORDER BY region_area.nom ASC, departement_area.code ASC',
                (new ResultSetMapping())
                    ->addScalarResult('departement_id', 'departement_id')
                    ->addScalarResult('departement_name', 'departement_name')
                    ->addScalarResult('departement_slug', 'departement_slug')
                    ->addScalarResult('region_id', 'region_id')
                    ->addScalarResult('region_name', 'region_name')
                    ->addScalarResult('region_slug', 'region_slug')
            )
            ->getResult()
        ;

        $regions = [];
        $previousRegionId = null;
        foreach ($rows as $row) {
            $regionId = $row['region_id'];
            if ($regionId !== $previousRegionId) {
                $regions[$regionId] = [
                    'id' => $row['region_id'],
                    'name' => $row['region_name'],
                    'slug' => $row['region_slug'],
                    'departements' => [],
                ];
            }

            $regions[$regionId]['departements'][] = [
                'id' => $row['departement_id'],
                'name' => $row['departement_name'],
                'slug' => $row['departement_slug'],
            ];

            $previousRegionId = $regionId;
        }

        // Reset des clés
        $regions = array_values($regions);

        return $regions;
    }
}
