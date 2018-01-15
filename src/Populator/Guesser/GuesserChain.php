<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Populator\Guesser;

use ApiExtension\Exception\GuesserNotFoundException;
use Faker\Generator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class GuesserChain extends AbstractGuesser
{
    /**
     * @var GuesserInterface[]
     */
    private $guessers;

    public function __construct(Generator $faker, array $guessers)
    {
        parent::__construct($faker);

        foreach ($guessers as $guesser) {
            if ($guesser instanceof GuesserAwareInterface) {
                $guesser->setGuesser($this);
            }
        }
        $this->guessers = $guessers;
    }

    public function supports(array $mapping): bool
    {
        return true;
    }

    public function getValue(array $mapping)
    {
        foreach ($this->guessers as $guesser) {
            if ($guesser->supports($mapping)) {
                return $guesser->getValue($mapping);
            }
        }

        throw new GuesserNotFoundException('No guesser found for mapping: '.print_r($mapping, true));
    }
}
