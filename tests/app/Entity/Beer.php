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

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"beer_read"}},
 *     "denormalization_context"={"groups"={"beer_write"}},
 *     "pagination_partial"=true
 * }, itemOperations={
 *     "delete",
 *     "put"={"validation_groups"={"Default"}},
 *     "get"
 * }, collectionOperations={
 *     "post"={"validation_groups"={"Default", "beer_create"}},
 *     "get"={"normalization_context"={"groups"={"beer_list_read"}}},
 * })
 * @ApiFilter(SearchFilter::class, properties={"name"})
 * @ORM\Entity
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class Beer
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"beer_list_read", "beer_read"})
     */
    private $id;

    /**
     * @var Company
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"beer_read", "beer_write"})
     * @Assert\NotNull
     */
    private $company;

    /**
     * @var string
     * @ORM\Column
     * @Groups({"beer_read", "beer_write", "beer_list_read"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var null|string
     * @ORM\Column(nullable=true)
     * @Groups({"beer_read", "beer_write"})
     * @Assert\NotBlank
     */
    private $type;

    /**
     * @var null|float
     * @ORM\Column(type="decimal", precision=8, scale=5, nullable=true)
     * @Groups({"beer_read", "beer_write"})
     */
    private $volume;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", name="is_active")
     * @Groups({"beer_read", "beer_write"})
     */
    private $active = false;

    /**
     * @var float
     * @ORM\Column(type="float")
     * @Groups({"beer_read", "beer_write"})
     * @Assert\NotBlank
     */
    private $price;

    /**
     * @var array
     * @ORM\Column(type="array")
     * @Groups({"beer_read", "beer_write"})
     * @Assert\NotBlank
     */
    private $ingredients = [];

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $stock = 100;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Groups({"beer_read", "beer_write"})
     */
    private $weight;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Groups({"beer_read", "beer_write"})
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Groups({"beer_read", "beer_write"})
     * @Assert\NotBlank
     * @Assert\Type("\DateTime")
     */
    private $createdAt;

    /**
     * @var string
     * @ORM\Column
     * @Groups({"beer_read", "beer_write"})
     * @Assert\NotBlank
     */
    private $currencyCode;

    /**
     * @var Collection|Image[]
     * @ORM\OneToMany(targetEntity="Image", mappedBy="beer")
     * @Groups({"beer_read", "beer_write"})
     */
    private $images;

    /**
     * @var null|string
     * @Groups({"beer_write"})
     * @Assert\NotBlank(groups={"beer_create"})
     */
    private $misc;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getVolume(): ?float
    {
        return $this->volume;
    }

    public function setVolume(?float $volume): void
    {
        $this->volume = $volume;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients): void
    {
        $this->ingredients = $ingredients;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return Image[]
     */
    public function getImages(): array
    {
        return $this->images->getValues();
    }

    public function addImage(Image $image): void
    {
        if (!$this->images->contains($image)) {
            $image->setBeer($this);
            $this->images[] = $image;
        }
    }

    public function removeImage(Image $image): void
    {
        $this->images->removeElement($image);
    }

    /**
     * @Groups({"beer_read"})
     */
    public function getNbImages(): int
    {
        return $this->images->count();
    }

    public function getMisc(): ?string
    {
        return $this->misc;
    }

    public function setMisc(?string $misc): void
    {
        $this->misc = $misc;
    }
}
