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
class DateTimeGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return \in_array($mapping['type'], ['datetime', 'date', 'time'], true);
    }

    public function getValue(array $mapping): \DateTime
    {
        return new \DateTime();
    }
}
