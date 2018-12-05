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
final class StringTransformer implements TransformerInterface
{
    public function supports(array $context, $value): bool
    {
        return \in_array($context['type'], ['string', 'text'], true);
    }

    public function toObject(array $context, $value): string
    {
        return (string) $value;
    }

    public function toScalar(array $context, $value): string
    {
        return $this->toObject($context, $value);
    }
}
