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
final class IntegerTypeGenerator implements TypeGeneratorInterface
{
    public function supports(array $context): bool
    {
        return \in_array($context['type'], ['integer', 'smallint', 'bigint'], true);
    }

    public function generate(array $context): array
    {
        $type = ['type' => ['integer']];
        if ($context['nullable'] ?? false) {
            $type['type'][] = 'null';
        }

        return $type;
    }
}
