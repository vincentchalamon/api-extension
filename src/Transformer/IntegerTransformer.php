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

namespace ApiExtension\Transformer;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class IntegerTransformer implements TransformerInterface
{
    public function supports(string $property, array $mapping, $value): bool
    {
        return in_array($mapping['type'], ['integer', 'smallint', 'bigint'], true);
    }

    public function transform(string $property, array $mapping, $value): int
    {
        return (int) $value;
    }
}
