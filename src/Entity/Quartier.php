<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Filter\CommuneFilter;
use Doctrine\ORM\Mapping as ORM;

/**
 * Quartier.
 *
 * @ORM\Table(name="quartier", indexes={@ORM\Index(columns={"commune_id"}), @ORM\Index(columns={"region_id"}), @ORM\Index(columns={"departement_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\QuartierRepository")
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
 * @ApiFilter(SearchFilter::class, properties={"code": "exact", "nom": "ipartial", "alias": "exact", "commune.code": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"nom": "ASC", "population": "DESC"})
 * @ApiFilter(CommuneFilter::class, properties={"commune"})
 * @ApiFilter(ExistsFilter::class, properties={"population"})
 */
class Quartier extends AbstractArea
{
    use Traits\AreaCentreTrait,
        Traits\AreaContourTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(name="population", type="integer", nullable=true)
     */
    public $population;

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
     * @var Departement
     *
     * @ORM\ManyToOne(targetEntity="Departement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="departement_id", referencedColumnName="id")
     * })
     */
    public $departement;

    /**
     * @var Commune
     *
     * @ORM\ManyToOne(targetEntity="Commune")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="commune_id", referencedColumnName="id")
     * })
     */
    public $commune;

    /**
     * Quartiers les plus proches.
     *
     * Champ non mappé, impacté au postLoad de Doctrine par App\EventListener\DoctrineSubscriber.
     *
     * @var array[]
     */
    public $nearestQuartiers;

    /**
     * @return int|null
     */
    public function getPopulation(): ?int
    {
        return $this->population;
    }

    /**
     * @param int|null $population
     *
     * @return self
     */
    public function setPopulation(?int $population): self
    {
        $this->population = $population;

        return $this;
    }

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

    /**
     * @return Departement|null
     */
    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    /**
     * @param Departement|null $departement
     *
     * @return self
     */
    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

        return $this;
    }

    /**
     * @return Commune|null
     */
    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    /**
     * @param Commune|null $commune
     *
     * @return self
     */
    public function setCommune(?Commune $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getNearestQuartiers(): array
    {
        return $this->nearestQuartiers;
    }

    /**
     * @param array $nearestQuartiers
     *
     * @return self
     */
    public function setNearestQuartiers(array $nearestQuartiers): self
    {
        $this->nearestQuartiers = $nearestQuartiers;

        return $this;
    }
}
