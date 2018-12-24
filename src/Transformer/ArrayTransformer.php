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
final class ArrayTransformer implements TransformerInterface
{
    public function supports(array $mapping, $value): bool
    {
        return \in_array($mapping['type'], ['array', 'simple_array'], true) && \is_string($value);
    }

    public function toObject(array $mapping, $value): array
    {
        return array_map('trim', explode(',', $value));
    }

    public function toScalar(array $mapping, $value): array
    {
        return $this->toObject($mapping, $value);
    }
}
