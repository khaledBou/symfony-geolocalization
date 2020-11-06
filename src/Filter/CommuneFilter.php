<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Filtre personnalisé sur la commune, par code postal.
 *
 * @see https://api-platform.com/docs/core/filters/#creating-custom-doctrine-orm-filters
 */
final class CommuneFilter extends AbstractContextAwareFilter
{
    /**
     * @inheritdoc
     */
    public function getDescription(string $resourceClass): array
    {
        $properties = $this->getProperties();
        if (!$properties) {
            return [];
        }

        $description = [];

        foreach ($properties as $property => $unused) {
            $propertyName = $this->normalizePropertyName($property);

            $description[$propertyName] = [
                // @see \ApiPlatform\Core\Api\FilterInterface pour les options disponibles
                'property' => $propertyName,
                'type' => 'string',
                'required' => false,
                'openapi' => [
                    'description' => 'Commune zipcode',
                ],
            ];
        }

        return $description;
    }

    /**
     * @inheritdoc
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // Otherwise filter is applied to order and page as well
        if ('commune' !== $property) {
            return;
        }

        // Generate a unique parameter name to avoid collisions with other filters
        $parameterName = $queryNameGenerator->generateParameterName($property);

        // Query builder
        $queryBuilder
            ->join('o.commune', 'c')
            ->andWhere(sprintf('JSONB_AG(c.codesPostaux, :%s) = TRUE', $parameterName))
            ->setParameter($parameterName, sprintf('["%s"]', $value))
        ;
    }
}
