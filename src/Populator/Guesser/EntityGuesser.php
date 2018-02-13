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

namespace ApiExtension\Populator\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class EntityGuesser implements GuesserInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function supports(array $mapping): bool
    {
        return null !== $mapping['targetEntity'] && in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::MANY_TO_ONE], true);
    }

    public function getValue(array $mapping)
    {
        $em = $this->registry->getManagerForClass($mapping['targetEntity']);
        $object = $em->getRepository($mapping['targetEntity'])->findOneBy([]);
        if (null === $object) {
            $object = $this->container->get('helper')->createObject(new \ReflectionClass($mapping['targetEntity']));
            $em->persist($object);
            $em->flush();
        }

        return $object;
    }
}
