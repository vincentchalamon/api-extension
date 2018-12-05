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

namespace ApiExtension\SchemaGenerator\TypeGenerator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Container\ContainerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionTypeGenerator implements TypeGeneratorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function supports(array $context): bool
    {
        return null !== $context['targetEntity'] && \in_array($context['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY], true);
    }

    public function generate(array $context): array
    {
        return [
            'type' => 'array',
            'items' => $this->container->get('schemaGenerator')->generate(new \ReflectionClass($context['targetEntity']), $context),
        ];
    }
}
