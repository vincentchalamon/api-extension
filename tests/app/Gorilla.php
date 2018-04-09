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

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"gorilla_read"}},
 *     "denormalization_context"={"groups"={"gorilla_write"}}
 * })
 */
class Gorilla
{
    /**
     * @Groups({"gorilla_read", "gorilla_write"})
     * @Assert\NotNull
     */
    private $banana;

    /**
     * @Groups({"gorilla_read", "gorilla_write"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @Groups({"gorilla_read", "gorilla_write"})
     */
    private $male = false;

    public function setBanana(Banana $banana)
    {
        $this->banana = $banana;
    }

    public function getBanana(): Banana
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
