<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class QueryBuilderChain implements QueryBuilderInterface
{
    /**
     * @var QueryBuilderInterface[]
     */
    private $objects;

    public function __construct($objects)
    {
        $this->objects = $objects;
    }

    public function apply(QueryBuilder $queryBuilder, $value, array $context): void
    {
        foreach ($this->objects as $object) {
            if ($object->supports($context['em']->getConnection()->getDriver()->getName())) {
                $object->apply($queryBuilder, $value, $context);
            }
        }
    }

    public function supports(string $driver): bool
    {
        return true;
    }
}
