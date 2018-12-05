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

use Faker\Generator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class FakerGuesser implements GuesserInterface
{
    protected $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function supports(array $context): bool
    {
        try {
            return !empty($context['name']) && $this->faker->getFormatter($context['name']);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }

    public function getValue(array $context)
    {
        return $this->faker->format($context['name']);
    }
}
