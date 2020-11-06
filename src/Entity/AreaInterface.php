<?php

namespace App\Entity;

/**
 * Définit le comportement d'une zone géographique.
 */
interface AreaInterface
{
    /**
     * @return string
     */
    public function getNom(): ?string;

    /**
     * @param string $nom
     *
     * @return self
     */
    public function setNom(string $nom): self;

    /**
     * @return string
     */
    public function getAlias(): ?string;

    /**
     * @param string $alias
     *
     * @return self
     */
    public function setAlias(string $alias): self;

    /**
     * @return string
     */
    public function getCode(): ?string;

    /**
     * @param string $code
     *
     * @return self
     */
    public function setCode(string $code): self;
}
