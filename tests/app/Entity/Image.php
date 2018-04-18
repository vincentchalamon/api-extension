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

namespace ApiExtension\App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"image_read"}},
 *     "denormalization_context"={"groups"={"image_write"}}
 * })
 * @ORM\Entity
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class Image
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column
     * @Groups({"image_read", "image_write", "beer_read", "beer_write"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var Beer
     * @ORM\ManyToOne(targetEntity="Beer", inversedBy="images")
     * @Groups({"image_read", "image_write"})
     * @Assert\NotNull
     */
    private $beer;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBeer(): Beer
    {
        return $this->beer;
    }

    public function setBeer(Beer $beer): void
    {
        $this->beer = $beer;
    }
}
