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
class IntegerGuesser extends FakerGuesser
{
    public function supports(array $context): bool
    {
        return \in_array($context['type'], ['int', 'integer', 'smallint', 'bigint'], true);
    }

    public function getValue(array $context): int
    {
        return $this->faker->numberBetween(0, $context['length'] ?? 2147483647);
    }
}
