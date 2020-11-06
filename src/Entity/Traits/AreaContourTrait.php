<?php

namespace App\Entity\Traits;

use App\Entity\AreaInterface;

/**
 * Définit un contour à une zone géographique.
 */
trait AreaContourTrait
{
    /**
     * @var array|null
     *
     * @ORM\Column(name="contour", type="jsonb", nullable=true)
     */
    public $contour;

    /**
     * @var string
     *
     * @ORM\Column(name="postgis_contour", type="geometry", options={"geometry_type"="MULTIPOLYGON"}, nullable=true)
     */
    public $postgisContour;

    /**
     * @return array|null
     */
    public function getContour(): ?array
    {
        return $this->contour;
    }

    /**
     * @param array|null $contour
     *
     * @return AreaInterface
     */
    public function setContour(?array $contour): AreaInterface
    {
        $this->contour = $contour;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostgisContour(): ?string
    {
        return $this->postgisContour;
    }

    /**
     * @param string|null $postgisContour
     *
     * @return AreaInterface
     */
    public function setPostgisContour(?string $postgisContour): AreaInterface
    {
        $this->postgisContour = $postgisContour;

        return $this;
    }
}
