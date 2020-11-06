<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zone géographique.
 *
 * @ORM\Entity
 * @ORM\Table(name="area", uniqueConstraints={@ORM\UniqueConstraint(columns={"type", "code"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "commune" = "Commune",
 *     "departement" = "Departement",
 *     "quartier" = "Quartier",
 *     "region" = "Region",
 * })
 */
abstract class AbstractArea implements AreaInterface
{
    use Traits\AreaNomTrait,
        Traits\AreaAliasTrait;

    /**
     * Clé primaire.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     */
    public $id;

    /**
     * Code de la zone géographique.
     *
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    public $code;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->nom;
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
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setCode(string $code): AreaInterface
    {
        $this->code = $code;

        return $this;
    }
}
