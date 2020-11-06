<?php

namespace App\Entity\Indicator;

use App\Entity\AreaInterface;
use App\Entity\Commune;

/**
 * Définit le comportement d'un indicateur KelQuartier.
 */
interface IndicatorInterface
{
    /**
     * @return AreaInterface
     */
    public function getArea(): ?AreaInterface;

    /**
     * @param AreaInterface $area
     *
     * @return self
     */
    public function setArea(AreaInterface $area): self;

    /**
     * @return string
     */
    public function getKelQuartierId(): ?string;

    /**
     * @param string $kelQuartierId
     *
     * @return self
     */
    public function setKelQuartierId(string $kelQuartierId): self;

    /**
     * @return \DateTimeInterface
     */
    public function getDate(): ?\DateTimeInterface;

    /**
     * @param \DateTimeInterface $date
     *
     * @return self
     */
    public function setDate(\DateTimeInterface $date): self;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     *
     * @return self
     */
    public function setLabel(string $label): self;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setValue($value): self;
}
