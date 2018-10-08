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

use Doctrine\DBAL\Types\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DateTimeTransformer implements TransformerInterface
{
    public function supports(array $mapping, $value): bool
    {
        return \in_array($mapping['type'], [Type::DATETIME, Type::DATE, Type::TIME], true);
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
