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

namespace ApiExtension\Bridge\Doctrine\ORM\Transformer;

use ApiExtension\Bridge\Doctrine\ORM\ObjectManager\EntityNotFoundException;
use ApiExtension\Bridge\Doctrine\ORM\QueryBuilder\QueryBuilderInterface;
use ApiExtension\Transformer\TransformerInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityTransformer implements TransformerInterface
{
    private $registry;
    private $iriConverter;
    private $queryBuilder;

    public function __construct(ManagerRegistry $registry, IriConverterInterface $iriConverter, QueryBuilderInterface $queryBuilder)
    {
        $this->registry = $registry;
        $this->iriConverter = $iriConverter;
        $this->queryBuilder = $queryBuilder;
    }

    public function supports(array $context, $value): bool
    {
        return null !== $context['targetEntity'] && \in_array($context['type'], [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::MANY_TO_ONE], true);
    }

    public function toObject(array $context, $value)
    {
        if (is_a($value, $context['targetEntity'])) {
            return $value;
        }

        $context['className'] = $context['targetEntity'];
        /** @var EntityManagerInterface $em */
        $context['em'] = $this->registry->getManagerForClass($context['className']);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $context['em']->getRepository($context['className'])->createQueryBuilder('o');
        $this->queryBuilder->apply($queryBuilder, $this->clean($value), $context);

        return $queryBuilder->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function toScalar(array $context, $value)
    {
        if (\is_array($value)) {
            return $value;
        }
        if (!\is_object($value)) {
            $value = $this->toObject($context, $value);
            if (null === $value) {
                throw new EntityNotFoundException(\sprintf('Unable to find an existing object of class %s with any value equal to %s.', $context['targetEntity'], $value));
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
