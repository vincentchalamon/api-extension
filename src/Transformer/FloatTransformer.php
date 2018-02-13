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
final class FloatTransformer implements TransformerInterface
{
    public function supports(string $property, array $mapping, $value): bool
    {
        return in_array($mapping['type'], ['float', 'decimal'], true);
    }

    public function transform(string $property, array $mapping, $value): float
    {
        return (float) $value;
    }
}
