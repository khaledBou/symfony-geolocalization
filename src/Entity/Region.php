<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;

/**
 * Région.
 *
 * @ORM\Table(name="region")
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 *
 * @ApiResource(
 *     collectionOperations={
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     },
 *     graphql={
 *         "item_query",
 *         "collection_query"
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"code": "exact", "nom": "ipartial", "alias": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"code": "ASC", "nom": "ASC"})
 */
class Region extends AbstractArea
{
    use Traits\AreaCentreTrait;

    /**
     * Régions les plus proches.
     *
     * Champ non mappé, impacté au postLoad de Doctrine par App\EventListener\DoctrineSubscriber.
     *
     * @var array[]
     */
    public $nearestRegions = array();

    /**
     * @return array[]
     */
    public function getNearestRegions(): array
    {
        return $this->nearestRegions;
    }

    /**
     * @param array $nearestRegions
     *
     * @return self
     */
    public function setNearestRegions(array $nearestRegions): self
    {
        $this->nearestRegions = $nearestRegions;

        return $this;
    }
}
