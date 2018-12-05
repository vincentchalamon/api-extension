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

namespace ApiExtension\Guesser;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class FloatGuesser extends FakerGuesser
{
    public function supports(array $context): bool
    {
        return 'float' === $context['type'];
    }

    public function getValue(array $context)
    {
        return $this->faker->randomFloat($context['scale'] ?? 0, 0, $context['precision'] ?? null);
    }
}
