<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class ItemGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Groups({"itemEncyclopedia"})
     */
    #[ORM\Column(type: 'string', length: 40, unique: true)]
    private $name;

    #[ORM\ManyToMany(targetEntity: Item::class, inversedBy: 'itemGroups')]
    private $items;

    #[ORM\Column(type: 'boolean')]
    private $isCraving = false;

    #[ORM\Column(type: 'boolean')]
    private $isGiftShop = false;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        $this->items->removeElement($item);

        return $this;
    }

    public function getIsCraving(): ?bool
    {
        return $this->isCraving;
    }

    public function setIsCraving(bool $isCraving): self
    {
        $this->isCraving = $isCraving;

        return $this;
    }

    public function getIsGiftShop(): ?bool
    {
        return $this->isGiftShop;
    }

    public function setIsGiftShop(bool $isGiftShop): self
    {
        $this->isGiftShop = $isGiftShop;

        return $this;
    }
}
