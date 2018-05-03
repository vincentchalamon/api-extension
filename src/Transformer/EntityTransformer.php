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

use ApiExtension\Exception\EntityNotFoundException;
use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityTransformer implements TransformerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function setIriConverter(IriConverterInterface $iriConverter): void
    {
        $this->iriConverter = $iriConverter;
    }

    public function supports(array $mapping, $value): bool
    {
        return null !== $mapping['targetEntity'] && in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::MANY_TO_ONE], true);
    }

    public function toObject(array $mapping, $value)
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
            $type = ($classMetadata->getFieldMapping($fieldName)['type'] ?? null);
            switch ($type) {
                case Type::TEXT:
                    $type = 'string';
                    break;
                case Type::DECIMAL:
                    $type = 'float';
                    break;
                case Type::SMALLINT:
                case Type::BIGINT:
                    $type = 'integer';
                    break;
            }
            if (gettype($value) === $type) {
                $queryBuilder->orWhere("o.$fieldName = :query")->setParameter('query', $value);
            }
        }

        return $queryBuilder->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function toScalar(array $mapping, $value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_object($value)) {
            $value = $this->toObject($mapping, $value);
            if (null === $value) {
                throw new EntityNotFoundException(sprintf('Unable to find an existing object of class %s with any value equal to %s.', $mapping['targetEntity'], $value));
            }
        }

        // todo What if I want to send a sub-object instead of just an iri?
        return $this->iriConverter->getIriFromItem($value);
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
