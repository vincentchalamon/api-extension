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

namespace ApiExtension\Transformer;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DateTimeTransformer implements TransformerInterface
{
    public function supports(array $mapping, $value): bool
    {
        return \in_array($mapping['type'], ['datetime', 'datetime_immutable', 'datetimetz', 'datetimetz_immutable', 'date', 'date_immutable', 'time', 'time_immutable'], true);
    }

    public function toObject(array $mapping, $value): \DateTime
    {
        if (!$value instanceof \DateTime) {
            $value = new \DateTime($value);
        }

        return $value;
    }

    public function toScalar(array $mapping, $value): string
    {
        return $value instanceof \DateTime ? $value->format('c') : $value;
    }
}
