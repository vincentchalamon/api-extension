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
final class IntegerTransformer implements TransformerInterface
{
    public function supports(array $mapping, $value): bool
    {
        return in_array($mapping['type'], ['int', Type::INTEGER, Type::SMALLINT, Type::BIGINT], true);
    }

    public function toObject(array $mapping, $value): int
    {
        return (int) $value;
    }

    public function toScalar(array $mapping, $value): int
    {
        return $this->toObject($mapping, $value);
    }
}
