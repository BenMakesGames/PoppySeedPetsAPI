<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Enchantment
{
    /**
     * @Groups({"myInventory", "marketItem", "greenhouseFertilizer"})
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem", "fireplaceFuel", "greenhouseFertilizer", "myPet", "fireplaceMantle", "dragonTreasure", "myAura"})
     */
    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private $name;

    /**
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem", "fireplaceFuel", "greenhouseFertilizer", "myPet", "fireplaceMantle", "dragonTreasure"})
     */
    #[ORM\Column(type: 'boolean')]
    private $isSuffix;

    /**
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem", "myPet"})
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\ItemTool', inversedBy: 'enchantment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $effects;

    /**
     * @Groups({"myInventory", "myPet", "itemEncyclopedia", "marketItem", "myAura", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
    #[ORM\ManyToOne(targetEntity: Aura::class)]
    private $aura;

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

    public function getEffects(): ?ItemTool
    {
        return $this->effects;
    }

    public function setEffects(ItemTool $effects): self
    {
        $this->effects = $effects;

        return $this;
    }

    public function getAura(): ?Aura
    {
        return $this->aura;
    }

    public function setAura(?Aura $aura): self
    {
        $this->aura = $aura;

        return $this;
    }
}
