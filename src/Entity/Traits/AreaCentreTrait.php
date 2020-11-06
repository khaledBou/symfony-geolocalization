<?php

namespace App\Entity\Traits;

use App\Entity\AreaInterface;

/**
 * Définit un centre à une zone géographique.
 */
trait AreaCentreTrait
{
    /**
     * @var array|null
     *
     * @ORM\Column(name="centre", type="jsonb", nullable=true)
     */
    public $centre;

    /**
     * @var string
     *
     * @ORM\Column(name="postgis_centre", type="geometry", options={"geometry_type"="POINT"}, nullable=true)
     */
    public $postgisCentre;

    /**
     * @return array|null
     */
    public function getCentre(): ?array
    {
        return $this->centre;
    }

    /**
     * @param array|null $centre
     *
     * @return AreaInterface
     */
    public function setCentre(?array $centre): AreaInterface
    {
        $this->centre = $centre;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostgisCentre(): ?string
    {
        return $this->postgisCentre;
    }

    /**
     * @param string|null $postgisCentre
     *
     * @return AreaInterface
     */
    public function setPostgisCentre(?string $postgisCentre): AreaInterface
    {
        $this->postgisCentre = $postgisCentre;

        return $this;
    }
}
