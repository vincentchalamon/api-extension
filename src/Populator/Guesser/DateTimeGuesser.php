<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Populator\Guesser;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class DateTimeGuesser extends AbstractGuesser
{
    public function supports(array $mapping): bool
    {
        return in_array($mapping['type'], ['datetime', 'date', 'time'], true);
    }

    public function getValue(array $mapping): string
    {
        return (new \DateTime())->format('c');
    }
}
