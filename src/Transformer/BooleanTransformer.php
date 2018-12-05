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
final class BooleanTransformer implements TransformerInterface
{
    public function supports(array $context, $value): bool
    {
        return 'boolean' === $context['type'];
    }

    public function toObject(array $context, $value): bool
    {
        return 'true' === $value;
    }

    public function toScalar(array $context, $value): bool
    {
        return $this->toObject($context, $value);
    }
}
