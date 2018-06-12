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
final class FloatTransformer implements TransformerInterface
{
    public function supports(array $mapping, $value): bool
    {
        return Type::FLOAT === $mapping['type'];
    }

    public function toObject(array $mapping, $value): float
    {
        return (float) $value;
    }

    public function toScalar(array $mapping, $value): float
    {
        return $this->toObject($mapping, $value);
    }
}
