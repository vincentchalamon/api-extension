<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface QueryBuilderInterface
{
    public function apply(QueryBuilder $queryBuilder, $value, array $context): void;

    public function supports(string $driver): bool;
}
