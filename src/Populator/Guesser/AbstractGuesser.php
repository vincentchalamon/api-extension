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

use Faker\Generator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class AbstractGuesser implements GuesserInterface
{
    protected $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }
}
