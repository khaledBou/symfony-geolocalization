<?php

namespace App\Entity\Indicator;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Indicateur KelQuartier "pourcentage".
 *
 * @ORM\Entity
 *
 * @ApiResource(
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
 */
class RatioIndicator extends AbstractIndicator
{
    /**
     * Valeur.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    public $value;

    /**
     * @return int
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return IndicatorInterface
     */
    public function setValue($value): IndicatorInterface
    {
        $this->value = $value;

        return $this;
    }
}
