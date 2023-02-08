<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EnchantmentRepository")
 */
class Enchantment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"myInventory", "marketItem", "greenhouseFertilizer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem", "fireplaceFuel", "greenhouseFertilizer", "myPet", "fireplaceMantle", "dragonTreasure", "myAura"})
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem", "fireplaceFuel", "greenhouseFertilizer", "myPet", "fireplaceMantle", "dragonTreasure"})
     */
    private $isSuffix;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemTool", inversedBy="enchantment", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myInventory", "itemEncyclopedia", "marketItem", "myPet"})
     */
    private $effects;

    /**
     * @ORM\ManyToOne(targetEntity=Aura::class)
     * @Groups({"myInventory", "myPet", "itemEncyclopedia", "marketItem", "myAura", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails"})
     */
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
