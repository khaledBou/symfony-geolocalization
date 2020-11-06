<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Filtre personnalisé sur les codes postaux d'une commune.
 *
 * @see https://api-platform.com/docs/core/filters/#creating-custom-doctrine-orm-filters
 */
final class CodesPostauxFilter extends AbstractContextAwareFilter
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
            $filterParameterNames = [
                $propertyName,
                $propertyName.'[]',
            ];

            foreach ($filterParameterNames as $filterParameterName) {
                $description[$filterParameterName] = [
                    // @see \ApiPlatform\Core\Api\FilterInterface pour les options disponibles
                    'property' => $propertyName,
                    'type' => 'string',
                    'required' => false,
                    'is_collection' => '[]' === substr((string) $filterParameterName, -2),
                ];
            }
        }

        return $description;
    }

    /**
     * @inheritdoc
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // Otherwise filter is applied to order and page as well
        if (!$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass) ) {
            return;
        }

        // La valeur du filtre
        $values = is_array($value) ? $value : [$value];

        // Les paramètres du filtre
        $or = [];
        $parameters = [];
        foreach ($values as $key => $value) {
            // Generate a unique parameter name to avoid collisions with other filters
            $parameterName = $queryNameGenerator->generateParameterName($property.$key);

            $or[] = sprintf('JSONB_AG(o.%s, :%s) = TRUE', $property, $parameterName);
            $parameters[$parameterName] = sprintf('["%s"]', $value);
        }

        // Query builder
        $queryBuilder->andWhere(sprintf('(%s)', implode(') OR (', $or)));
        foreach ($parameters as $parameterName => $parameterValue) {
            $queryBuilder->setParameter($parameterName, $parameterValue);
        }
    }
}
