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

use ApiExtension\Guesser\GuesserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class CollectionGuesser implements GuesserInterface
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function supports(array $context): bool
    {
        return \in_array($context['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY], true) && isset($context['targetEntity']) && !empty($context['targetEntity']);
    }

    public function getValue(array $context): array
    {
        return $this->registry->getManagerForClass($context['targetEntity'])->getRepository($context['targetEntity'])->findBy([], null, \mt_rand(3, 10));
    }
}
