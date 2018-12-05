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
class StringGuesser extends FakerGuesser
{
    public function supports(array $context): bool
    {
        return \in_array($context['type'], ['string', 'text'], true);
    }

    public function getValue(array $context): string
    {
        if ('text' === $context['type']) {
            return $this->faker->paragraph();
        }
        $length = $context['length'] ?? 200;

        return $this->faker->text($length < 5 ? 5 : $length);
    }
}
