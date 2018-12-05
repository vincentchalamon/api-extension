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
class ArrayGuesser extends FakerGuesser
{
    public function supports(array $context): bool
    {
        return \in_array($context['type'], ['array', 'simple_array'], true);
    }

    public function getValue(array $context): array
    {
        return \array_fill(2, \mt_rand(3, 10), $this->faker->word);
    }
}
