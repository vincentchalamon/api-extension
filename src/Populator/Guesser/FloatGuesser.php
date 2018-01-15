<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Populator\Guesser;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class FloatGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return 'float' === $mapping['type'];
    }

    public function getValue(array $mapping): float
    {
        return $this->faker->randomFloat($mapping['scale'] ?? 0, 0, $mapping['precision'] ?? null);
    }
}
