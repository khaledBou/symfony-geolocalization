<?php

namespace App\Entity\Indicator;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Indicateur KelQuartier "chaîne de caractères".
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
class StringIndicator extends AbstractIndicator
{
    /**
     * Valeur.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    public $value;

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return IndicatorInterface
     */
    public function setValue($value): IndicatorInterface
    {
        $this->value = $value;

        return $this;
    }
}
