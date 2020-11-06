<?php

namespace App\Repository;

use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Repository des régions.
 */
class RegionRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    /**
     * Récupère les régions les plus proches d'une région.
     *
     * @param Refion $region La région à partir de laquelle chercher les régions les plus proches
     * @param int    $limit  Le nombre maximal de régions à retourner
     *
     * @return string[]
     */
    public function findNearestFromRegion(Region $region, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.id, r.nom, r.alias')
            ->andWhere('r != :region')
            ->orderBy('ST_Distance(r.postgisCentre, :postgis_centre)', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('region', $region)
            ->setParameter('postgis_centre', $region->getPostgisCentre())
            ->getQuery()
            ->getScalarResult()
        ;
    }
}
