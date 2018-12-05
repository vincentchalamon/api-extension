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

namespace ApiExtension\Bridge\Doctrine\ORM\Guesser;

use ApiExtension\Bridge\Doctrine\ORM\ObjectManager\ObjectManager;
use ApiExtension\Guesser\GuesserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class EntityGuesser implements GuesserInterface
{
    private $registry;
    private $objectManager;

    public function __construct(ManagerRegistry $registry, ObjectManager $objectManager)
    {
        $this->registry = $registry;
        $this->objectManager = $objectManager;
    }

    public function supports(array $context): bool
    {
        return \in_array($context['type'], [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::MANY_TO_ONE], true) && isset($context['targetEntity']) && !empty($context['targetEntity']);
    }

    public function getValue(array $context)
    {
        $em = $this->registry->getManagerForClass($context['targetEntity']);
        $object = $em->getRepository($context['targetEntity'])->findOneBy([]);
        if (null === $object) {
            $object = $this->objectManager->fake(new \ReflectionClass($context['targetEntity']));
            $em->persist($object);
            $em->flush();
        }

        return $object;
    }
}
