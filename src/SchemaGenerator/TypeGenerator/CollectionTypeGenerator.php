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

namespace ApiExtension\SchemaGenerator\TypeGenerator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionTypeGenerator implements TypeGeneratorInterface
{
    public function supports(string $property, array $mapping, array $context = []): bool
    {
        return null !== $mapping['targetEntity'] && in_array($mapping['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY], true);
    }

    public function generate(string $property, array $mapping, array $context = []): array
    {
        return [
            'type' => 'array',
            'items' => [
                'type' => ['object', 'string'],
//                'properties' => [], // todo Generate properties
            ],
        ];
    }
}
