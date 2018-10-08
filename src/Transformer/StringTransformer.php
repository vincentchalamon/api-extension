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
final class StringTransformer implements TransformerInterface
{
    public function supports(array $mapping, $value): bool
    {
        return \in_array($mapping['type'], [Type::STRING, Type::TEXT], true);
    }

    public function toObject(array $mapping, $value): string
    {
        return (string) $value;
    }

    public function toScalar(array $mapping, $value): string
    {
        return $this->toObject($mapping, $value);
    }
}
