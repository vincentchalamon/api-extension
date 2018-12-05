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
final class GuesserChain implements GuesserInterface
{
    /**
     * @var GuesserInterface[]
     */
    private $guessers;

    public function __construct(array $guessers)
    {
        foreach ($guessers as $guesser) {
            if ($guesser instanceof GuesserAwareInterface) {
                $guesser->setGuesser($this);
            }
        }
        $this->guessers = $guessers;
    }

    public function getValue(array $context)
    {
        foreach ($this->guessers as $guesser) {
            if ($guesser->supports($context)) {
                return $guesser->getValue($context);
            }
        }

        throw new GuesserNotFoundException();
    }

    public function supports(array $context): bool
    {
        return true;
    }
}
