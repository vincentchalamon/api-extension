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

namespace ApiExtension\SchemaGenerator\TypeGenerator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NumberTypeGenerator implements TypeGeneratorInterface
{
    public function supports(string $property, array $mapping, array $context = []): bool
    {
        return in_array($mapping['type'], ['float', 'decimal'], true);
    }

    public function generate(string $property, array $mapping, array $context = []): array
    {
        $type = ['type' => ['number']];
        if (true === $mapping['nullable']) {
            $type['type'][] = 'null';
        }

        return $type;
    }
}
