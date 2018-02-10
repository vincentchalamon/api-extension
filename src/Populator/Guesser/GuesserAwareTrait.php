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
trait GuesserAwareTrait
{
    /**
     * @var GuesserInterface
     */
    private $guesser;

    public function setGuesser(GuesserInterface $guesser): void
    {
        $this->guesser = $guesser;
    }
}
