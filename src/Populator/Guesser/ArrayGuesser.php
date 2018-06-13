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

use Doctrine\DBAL\Types\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class ArrayGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return in_array($mapping['type'], [Type::TARRAY, Type::SIMPLE_ARRAY, Type::JSON_ARRAY], true);
    }

    public function getValue(array $mapping): array
    {
        return array_fill(2, mt_rand(3, 10), $this->faker->word);
    }
}
