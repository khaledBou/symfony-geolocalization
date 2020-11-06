<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\CommuneController;
use App\Filter\CodesPostauxFilter;
use Doctrine\ORM\Mapping as ORM;

/**
 * Commune.
 *
 * @ORM\Table(name="commune", indexes={@ORM\Index(columns={"departement_id"}), @ORM\Index(columns={"region_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\CommuneRepository")
 *
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "get_autocomplete"={
 *             "method"="GET",
 *             "route_name"="get_collection_autocomplete",
 *             "controller"=CommuneController::class,
 *             "openapi_context"={
 *                 "summary"="Performs an autocomplete search from MapBox API and associates the results with our Commune resources.",
 *                 "parameters"={
 *                     {
 *                         "in"="path",
 *                         "name"="query",
 *                         "type"="string",
 *                         "required"=true,
 *                     },
 *                 },
 *                 "responses"={
 *                     "200"={
 *                         "description"="Associative array containing our Commune resources along with MapBox results.",
 *                     },
 *                 },
 *             },
 *             "pagination_enabled"=false,
 *         },
 *         "get_arrondissements"={
 *             "method"="GET",
 *             "route_name"="get_collection_arrondissements",
 *             "controller"=CommuneController::class,
 *             "openapi_context"={
 *                 "summary"="Retrieves arrondissements and the Commune resources they belongs to.",
 *                 "parameters"={
 *                 },
 *                 "responses"={
 *                     "200"={
 *                         "description"="Array of associative arrays containing Commune resources (key 'commune') and their arrondissements (key 'arrondissements').",
 *                     },
 *                 },
 *             },
 *             "pagination_enabled"=false,
 *         },
 *     },
 *     itemOperations={
 *         "get"
 *     },
 *     graphql={
 *         "item_query",
 *         "collection_query"
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"code": "exact", "nom": "istart", "alias": "exact", "departement": "exact"})
 * @ApiFilter(BooleanFilter::class, properties={"arrondissements", "arrondissement"})
 * @ApiFilter(OrderFilter::class, properties={"nom": "ASC", "population": "DESC"})
 * @ApiFilter(CodesPostauxFilter::class, properties={"codesPostaux"})
 * @ApiFilter(ExistsFilter::class, properties={"population"})
 */
class Commune extends AbstractArea
{
    use Traits\AreaCentreTrait,
        Traits\AreaContourTrait;

    /**
     * @var int|null
     *
     * @ORM\Column(name="surface", type="integer", nullable=true)
     */
    public $surface;

    /**
     * @var int|null
     *
     * @ORM\Column(name="population", type="integer", nullable=true)
     */
    public $population;

    /**
     * @var int|null
     *
     * @ORM\Column(name="code_postal", type="integer", nullable=true)
     */
    public $codePostal;

    /**
     * @var array
     *
     * @ORM\Column(name="codes_postaux", type="jsonb", nullable=false)
     */
    public $codesPostaux;

    /**
     * Indique la présence d'arrondissements dans cette commune.
     *
     * @var bool
     *
     * @ORM\Column(name="arrondissements", type="boolean", nullable=false)
     */
    public $arrondissements = false;

    /**
     * Indique si cette commune représente un arrondissement.
     *
     * @var bool
     *
     * @ORM\Column(name="arrondissement", type="boolean", nullable=false)
     */
    public $arrondissement = false;

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
     * Communes les plus proches.
     *
     * Champ non mappé, impacté au postLoad de Doctrine par App\EventListener\DoctrineSubscriber.
     *
     * @var array[]
     */
    public $nearestCommunes = array();

    /**
     * @return int|null
     */
    public function getSurface(): ?int
    {
        return $this->surface;
    }

    /**
     * @param int|null $surface
     *
     * @return self
     */
    public function setSurface(?int $surface): self
    {
        $this->surface = $surface;

        return $this;
    }

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
     * @return int|null
     */
    public function getCodePostal(): ?int
    {
        return $this->codePostal;
    }

    /**
     * @param int|null $codePostal
     *
     * @return self
     */
    public function setCodePostal(?int $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return array
     */
    public function getCodesPostaux(): ?array
    {
        return $this->codesPostaux;
    }

    /**
     * @param array $codesPostaux
     *
     * @return self
     */
    public function setCodesPostaux(array $codesPostaux): self
    {
        $this->codesPostaux = $codesPostaux;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasArrondissements(): ?bool
    {
        return $this->arrondissements;
    }

    /**
     * @param bool $arrondissements
     *
     * @return self
     */
    public function setArrondissements(bool $arrondissements): self
    {
        $this->arrondissements = $arrondissements;

        return $this;
    }

    /**
     * @return bool
     */
    public function isArrondissement(): ?bool
    {
        return $this->arrondissement;
    }

    /**
     * @param bool $arrondissement
     *
     * @return self
     */
    public function setArrondissement(bool $arrondissement): self
    {
        $this->arrondissement = $arrondissement;

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
     * @return array[]
     */
    public function getNearestCommunes(): array
    {
        return $this->nearestCommunes;
    }

    /**
     * @param array $nearestCommunes
     *
     * @return self
     */
    public function setNearestCommunes(array $nearestCommunes): self
    {
        $this->nearestCommunes = $nearestCommunes;

        return $this;
    }
}
