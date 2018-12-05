<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\ORM\QueryBuilder;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class PostgreSQLQueryBuilder implements QueryBuilderInterface
{
    public function apply(QueryBuilder $queryBuilder, $value, array $context): void
    {
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $context['em']->getClassMetadata($context['className']);
        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $type = ($classMetadata->getFieldMapping($fieldName)['type'] ?? null);
            switch ($type) {
                default:
                    $type = 'not-supported';
                    break;
                case Type::STRING:
                case Type::TEXT:
                    $type = 'string';
                    break;
                case Type::GUID:
                    if (!\preg_match('/^(\{{0,1}([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}\}{0,1})$/', $value)) {
                        $type = 'invalid';
                    }
                    break;
                case Type::DATE:
                case Type::DATETIME:
                case Type::DATETIMETZ:
                    if (!\preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/', $value)) {
                        $type = 'invalid';
                    }
                    break;
                case Type::FLOAT:
                case Type::DECIMAL:
                    $type = 'float';
                    break;
                case Type::BOOLEAN:
                    $type = 'boolean';
                    break;
                case Type::SMALLINT:
                case Type::BIGINT:
                case 'integer':
                    $type = 'integer';
                    break;
            }
            if (\gettype($value) === $type) {
                $queryBuilder->orWhere("o.$fieldName = :query")->setParameter('query', $value);
            }
        }
    }

    public function supports(string $driver): bool
    {
        return 'pdo_pgsql' === $driver;
    }
}
