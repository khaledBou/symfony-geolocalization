<?php

namespace App\Entity\Traits;

use App\Entity\AreaInterface;

/**
 * Définit un nom à une zone géographique.
 */
trait AreaNomTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=false)
     */
    public $nom;

    /**
     * @return string
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     *
     * @return AreaInterface
     */
    public function setNom(string $nom): AreaInterface
    {
        $this->nom = $nom;

        return $this;
    }
}
