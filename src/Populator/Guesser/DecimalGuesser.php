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

    /**
     * Must be parsed as string for Doctrine.
     */
    public function getValue(array $mapping): string
    {
        return (string) $this->faker->randomFloat($mapping['scale'] ?? 0, 0, $mapping['precision'] ?? null);
    }
}
