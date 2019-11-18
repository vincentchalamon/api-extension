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
 * @author Mathieu Dewet <mathieu.dewet@gmail.com>
 */
final class JsonTransformer implements TransformerInterface
{
    public function supports(array $mapping, $value): bool
    {
        $typeClass = Type::class;

        return \in_array($mapping['type'], \defined("$typeClass::JSON") ? [Type::JSON, Type::JSON_ARRAY] : [Type::JSON_ARRAY], true) && \is_string($value);
    }

    public function toObject(array $mapping, $value): array
    {
        return \json_decode($value, true);
    }

    public function toScalar(array $mapping, $value): array
    {
        return $this->toObject($mapping, $value);
    }
}
