<?php

namespace App\Repository;

use App\Entity\Quartier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Repository des quartiers.
 */
class QuartierRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quartier::class);
    }

    /**
     * Récupère les coordonnées GPS (longitude, latitude)
     * des quartiers pour lesquels une mise à jour des indicateurs est nécessaire.
     *
     * @todo : mutualiser avec la méthode du même nom dans CommuneRepository
     *
     * @param int      $age   Âge des indicateurs à partir duquel une mise à jour est nécessaire (en jours)
     * @param int|null $limit Nombre de quartiers pour lesquels mettre à jour les indicateurs (passer null pour désactiver cette limite)
     *
     * @return array[]
     */
    public function getCoordinatesForIndicatorsToUpdate(int $age, ?int $limit): array
    {
        return $this
            ->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'SELECT DISTINCT q.id, q.centre->\'coordinates\' AS coordinates
                    FROM quartier q
                    LEFT JOIN indicator i ON i.area_id = q.id
                    WHERE i.id IS NULL OR i.date < NOW() - INTERVAL \'%d days\'
                    ORDER BY q.id ASC
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
     * Récupère les quartiers les plus proches d'un quartier.
     *
     * @param Quartier $quartier Le quartier à partir duquel chercher les quartiers les plus proches
     * @param int      $limit    Le nombre maximal de quartiers à retourner
     *
     * @return string[]
     */
    public function findNearestFromQuartier(Quartier $quartier, int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->select('q.id, q.nom, q.alias')
            ->andWhere('q != :quartier')
            ->orderBy('ST_Distance(q.postgisContour, :postgis_centre)', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('quartier', $quartier)
            ->setParameter('postgis_centre', $quartier->getPostgisCentre())
            ->getQuery()
            ->getScalarResult()
        ;
    }
}
