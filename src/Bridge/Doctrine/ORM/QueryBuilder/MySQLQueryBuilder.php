<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class MySQLQueryBuilder implements QueryBuilderInterface
{
    public function apply(QueryBuilder $queryBuilder, $value, array $context): void
    {
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $context['em']->getClassMetadata($context['className']);
        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $queryBuilder->orWhere("o.$fieldName = :query")->setParameter('query', $value);
        }
    }

    public function supports(string $driver): bool
    {
        return \in_array($driver, ['mysqli', 'pdo_mysql', 'drizzle_pdo_mysql', 'pdo_sqlite'], true);
    }
}
