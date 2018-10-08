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
class IntegerGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return \in_array($mapping['type'], ['int', 'integer', 'smallint', 'bigint'], true);
    }

    public function getValue(array $mapping): int
    {
        return $this->faker->numberBetween(0, $mapping['length'] ?? 2147483647);
    }
}
