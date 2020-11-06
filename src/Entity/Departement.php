<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;

/**
 * Département.
 *
 * @ORM\Table(name="departement", indexes={@ORM\Index(columns={"region_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\DepartementRepository")
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
 * @ApiFilter(SearchFilter::class, properties={"code": "exact", "nom": "ipartial", "alias": "exact", "centre": "exact", "region": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"code": "ASC", "nom": "ASC"})
 */
class Departement extends AbstractArea
{
    use Traits\AreaCentreTrait,
        Traits\AreaContourTrait;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Region")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     * })
     */
    public $region;

    /**
     * @return Region|null
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param Region|null $region
     *
     * @return self
     */
    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }
}
