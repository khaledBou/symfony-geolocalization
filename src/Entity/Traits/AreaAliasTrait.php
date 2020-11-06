<?php

namespace App\Entity\Traits;

use App\Entity\AreaInterface;

/**
 * Définit un alias à une zone géographique.
 */
trait AreaAliasTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255, nullable=false)
     */
    public $alias;

    /**
     * @return string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     *
     * @return AreaInterface
     */
    public function setAlias(string $alias): AreaInterface
    {
        $this->alias = $alias;

        return $this;
    }
}
