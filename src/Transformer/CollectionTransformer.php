<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionTransformer implements TransformerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function supports(string $property, array $mapping, $value): bool
    {
        return null !== $mapping['targetEntity'] && in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY], true);
    }

    public function transform(string $property, array $mapping, $value): ArrayCollection
    {
        if (is_a($value, Collection::class)) {
            return $value;
        }

        if (is_array($value)) {
            return new ArrayCollection($value);
        }

        $values = array_map('trim', explode(',', $value));
        $className = $mapping['targetEntity'];
        $em = $this->registry->getManagerForClass($className);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository($className)->createQueryBuilder('o');
        foreach ($em->getClassMetadata($className)->getFieldNames() as $fieldName) {
            $queryBuilder->orWhere($queryBuilder->expr()->in("o.$fieldName", ':query'));
        }

        return new ArrayCollection($queryBuilder
            ->setParameter('query', $values)
            ->getQuery()
            ->getResult());
    }
}
