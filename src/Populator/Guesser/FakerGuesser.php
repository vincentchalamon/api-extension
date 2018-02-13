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
class FakerGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        try {
            return null !== $mapping['fieldName'] && $this->faker->getFormatter($mapping['fieldName']);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }

    public function getValue(array $mapping)
    {
        return $this->faker->format($mapping['fieldName']);
    }
}
