<?php

/*
 * This file is part of the API Extension project.
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

        $values = array_values(array_map([$this, 'clean'], explode(',', $value)));
        $className = $mapping['targetEntity'];
        $em = $this->registry->getManagerForClass($className);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository($className)->createQueryBuilder('o');
        $classMetadata = $em->getClassMetadata($className);
        foreach ($em->getClassMetadata($className)->getFieldNames() as $fieldName) {
            // todo Fix this shit
            $type = ($classMetadata->getFieldMapping($fieldName)['type'] ?? null);
            if ('text' === $type) {
                $type = 'string';
            }
            if ('decimal' === $type) {
                $type = 'float';
            }
            if (in_array($type, ['smallint', 'bigint'], true)) {
                $type = 'integer';
            }
            if (gettype($values[0]) === $type) {
                $queryBuilder->orWhere($queryBuilder->expr()->in("o.$fieldName", ':query'));
                $queryBuilder->setParameter('query', $values);
            }
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    private function clean($value)
    {
        $value = trim((string) $value);
        if (empty($value)) {
            return '';
        }
        if (!preg_match('/[^0-9.]+/', $value)) {
            return preg_match('/[.]+/', $value) ? (float) $value : (int) $value;
        }
        if ('true' === $value) {
            return true;
        }
        if ('false' === $value) {
            return false;
        }

        return (string) $value;
    }
}
