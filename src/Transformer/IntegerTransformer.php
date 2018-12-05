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
final class IntegerTransformer implements TransformerInterface
{
    public function supports(array $context, $value): bool
    {
        return \in_array($context['type'], ['int', 'integer', 'smallint', 'bigint'], true);
    }

    public function toObject(array $context, $value): int
    {
        return (int) $value;
    }

    public function toScalar(array $context, $value): int
    {
        return $this->toObject($context, $value);
    }
}
