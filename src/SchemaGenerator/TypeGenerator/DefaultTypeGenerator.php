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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DefaultTypeGenerator implements TypeGeneratorInterface
{
    public function supports(array $mapping, array $context = []): bool
    {
        return true;
    }

    public function generate(array $mapping, array $context = []): array
    {
        if (!\in_array($mapping['type'], ['integer', 'number', 'boolean', 'object', 'array', 'string', 'null', 'any'], true)) {
            $mapping['type'] = 'any';
        }
        $type = ['type' => $mapping['type']];
        if ($mapping['nullable'] ?? false) {
            if (!\is_array($type['type'])) {
                $type['type'] = [$type['type']];
            }
            $type['type'][] = 'null';
            $type['type'] = array_unique($type['type']);
        }

        return $type;
    }
}
