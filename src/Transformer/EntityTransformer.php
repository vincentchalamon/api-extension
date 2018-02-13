<?php

declare(strict_types=1);

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiExtension\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityTransformer implements TransformerInterface
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
        return null !== $mapping['targetEntity'] && in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::MANY_TO_ONE], true);
    }

    public function transform(string $property, array $mapping, $value)
    {
        if (is_a($value, $mapping['targetEntity'])) {
            return $value;
        }

        $value = $this->clean($value);
        $className = $mapping['targetEntity'];
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($className);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository($className)->createQueryBuilder('o');
        $classMetadata = $em->getClassMetadata($className);
        foreach ($classMetadata->getFieldNames() as $fieldName) {
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
            if (gettype($value) === $type) {
                $queryBuilder->orWhere("o.$fieldName = :query")->setParameter('query', $value);
            }
        }

        return $queryBuilder->setMaxResults(1)->getQuery()->getOneOrNullResult();
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
