<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\SchemaGenerator\TypeGenerator;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class DateTimeTypeGenerator implements TypeGeneratorInterface
{
    public function supports(string $property, array $mapping, array $context = []): bool
    {
        return in_array($mapping['type'], ['datetime', 'date', 'time'], true);
    }

    public function generate(string $property, array $mapping, array $context = []): array
    {
        $type = [
            'type' => ['string'],
            'pattern' => '^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}\\+00:00$',
        ];
        if (true === $mapping['nullable']) {
            $type['type'][] = 'null';
        }

        return $type;
    }
}
