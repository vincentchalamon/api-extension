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
 *     "normalization_context"={"groups"={"banana_read"}},
 *     "denormalization_context"={"groups"={"banana_write"}}
 * })
 */
class Banana
{
    /**
     * @Groups({"banana_read", "banana_write"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @Groups({"banana_read", "banana_write"})
     */
    private $male = false;

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
