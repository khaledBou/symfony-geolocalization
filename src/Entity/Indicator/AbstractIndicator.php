<?php

namespace App\Entity\Indicator;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\AbstractArea;
use App\Entity\AreaInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Indicateur KelQuartier.
 *
 * @ORM\Entity
 * @ORM\Table(name="indicator", uniqueConstraints={@ORM\UniqueConstraint(columns={"area_id", "kel_quartier_id"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "int" = "IntIndicator",
 *     "ratio" = "RatioIndicator",
 *     "string" = "StringIndicator",
 *     "text" = "TextIndicator",
 * })
 *
 * @ApiResource(
 *     shortName="Indicateur",
 *     collectionOperations={
 *         "get",
 *     },
 *     itemOperations={
 *         "get",
 *     },
 *     graphql={
 *         "item_query",
 *         "collection_query",
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"area": "exact", "kelQuartierId": "exact"})
 */
abstract class AbstractIndicator implements IndicatorInterface
{
    // Libellés des indicateurs, selon leur identifiant KelQuartier
    const LABELS = [
        '19' => 'Part des propriétaires',
        '21' => 'Part des résidences secondaires',
        '142' => 'Part des maisons',
        '143' => 'Part des appartements',
        '26' => 'Revenu mensuel moyen',
        '7' => 'Âge moyen',
        '3' => 'Densité de population',
        '53' => 'Part des bacheliers',
        '29' => 'Cadres',
        '11' => 'Nombre d\'habitants',
        '145' => 'Population',
        '135' => 'Part des habitants de moins de 25 ans',
        '136' => 'Part des habitants de 25 à 55 ans',
        '137' => 'Part des habitants de plus de 55 ans',
        '216' => 'Espaces verts',
        '217' => 'Transports',
        '34' => 'Restaurants et cafés',
        '110' => 'Médecins généralistes',
        '232' => 'Nombre de commerces de proximité',
        'descriptif' => 'Descriptif',
        'photo_url' => 'URL de la photo',
        'photo_title' => 'Titre de la photo',
        'prix_maison_bas' => 'Prix maisons (bas)',
        'prix_maison_moyen' => 'Prix maisons (moyen)',
        'prix_maison_haut' => 'Prix maisons (haut)',
        'prix_appart_bas' => 'Prix appartements (bas)',
        'prix_appart_moyen' => 'Prix appartements (moyen)',
        'prix_appart_haut' => 'Prix appartements (haut)',
    ];

    /**
     * Identifiant.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * Zone géographique concernée par l'indicateur.
     *
     * @var AreaInterface
     *
     * @ORM\ManyToOne(targetEntity=AbstractArea::class)
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", nullable=false)
     */
    public $area;

    /**
     * Identifiant de l'indicateur dans KelQuartier.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    public $kelQuartierId;

    /**
     * Date d'import de l'indicateur.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    public $date;

    /**
     * Libellé de l'indicateur.
     *
     * Champ non mappé, impacté au postLoad de Doctrine par App\EventListener\DoctrineSubscriber.
     *
     * @var string
     */
    public $label;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->date = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getArea(): ?AreaInterface
    {
        return $this->area;
    }

    /**
     * @inheritdoc
     */
    public function setArea(AreaInterface $area): IndicatorInterface
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getKelQuartierId(): ?string
    {
        return $this->kelQuartierId;
    }

    /**
     * @inheritdoc
     */
    public function setKelQuartierId(string $kelQuartierId): IndicatorInterface
    {
        $this->kelQuartierId = $kelQuartierId;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @inheritdoc
     */
    public function setDate(\DateTimeInterface $date): IndicatorInterface
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function setLabel(string $label): IndicatorInterface
    {
        $this->label = $label;

        return $this;
    }
}
