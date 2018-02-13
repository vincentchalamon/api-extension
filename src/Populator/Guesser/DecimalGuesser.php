<?php

declare(strict_types=1);

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiExtension\Populator\Guesser;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class DecimalGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return 'decimal' === $mapping['type'];
    }

    public function getValue(array $mapping): string
    {
        return (string) $this->faker->randomFloat($mapping['scale'] ?? 0, 0, $mapping['precision'] ?? null);
    }
}
