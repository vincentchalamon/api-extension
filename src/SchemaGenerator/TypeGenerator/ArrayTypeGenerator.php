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

use Doctrine\DBAL\Types\Type;

/**
 * @author Jordan Aubert <jordan@les-tilleuls.coop>
 */
final class ArrayTypeGenerator implements TypeGeneratorInterface
{
    public function supports(array $context): bool
    {
        return \in_array($context['type'], [Type::TARRAY, Type::SIMPLE_ARRAY, Type::JSON_ARRAY], true);
    }

    public function generate(array $context): array
    {
        $type = ['type' => ['array', 'object']];
        if ($context['nullable'] ?? false) {
            $type['type'][] = 'null';
        }

        return $type;
    }
}
