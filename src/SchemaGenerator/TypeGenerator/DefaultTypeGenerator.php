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
    public function supports(array $context): bool
    {
        return true;
    }

    public function generate(array $context): array
    {
        if (!\in_array($context['type'], ['integer', 'number', 'boolean', 'object', 'array', 'string', 'null', 'any'], true)) {
            $context['type'] = 'any';
        }
        $type = ['type' => $context['type']];
        if ($context['nullable'] ?? false) {
            if (!\is_array($type['type'])) {
                $type['type'] = [$type['type']];
            }
            $type['type'][] = 'null';
            $type['type'] = array_unique($type['type']);
        }

        return $type;
    }
}
