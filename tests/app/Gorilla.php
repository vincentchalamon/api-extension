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

namespace ApiExtension\App;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class Gorilla
{
    private $banana;
    private $name;
    private $male = false;

    public function setBanana(string $banana)
    {
        $this->banana = $banana;
    }

    public function getBanana(): string
    {
        return $this->banana;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setMale(bool $male)
    {
        $this->male = $male;
    }

    public function isMale(): bool
    {
        return $this->male;
    }
}
