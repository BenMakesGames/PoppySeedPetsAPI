<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Spice
{
    #[Groups(["marketItem", "greenhouseFertilizer", "fireplaceFuel"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["myInventory", "marketItem", "itemEncyclopedia"])]
    #[ORM\OneToOne(targetEntity: ItemFood::class, inversedBy: 'spice', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $effects;

    #[Groups(["myInventory", "marketItem", "itemEncyclopedia", "myPet", "dragonTreasure", "greenhouseFertilizer", "fireplaceFuel"])]
    #[ORM\Column(type: 'string', length: 20)]
    private $name;

    #[Groups(["myInventory", "marketItem", "itemEncyclopedia", "myPet", "dragonTreasure", "greenhouseFertilizer", "fireplaceFuel"])]
    #[ORM\Column(type: 'boolean')]
    private $isSuffix;

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

    public function getIsSuffix(): ?bool
    {
        return $this->isSuffix;
    }

    public function setIsSuffix(bool $isSuffix): self
    {
        $this->isSuffix = $isSuffix;

        return $this;
    }

    public function getEffects(): ?ItemFood
    {
        return $this->effects;
    }

    public function setEffects(ItemFood $effects): self
    {
        $this->effects = $effects;

        return $this;
    }
}
