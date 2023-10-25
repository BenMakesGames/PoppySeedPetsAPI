<?php

namespace App\Entity;

use App\Repository\SpiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=SpiceRepository::class)
 */
class Spice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"marketItem", "greenhouseFertilizer"})
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=ItemFood::class, inversedBy="spice", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myInventory", "marketItem", "itemEncyclopedia"})
     */
    private $effects;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"myInventory", "marketItem", "itemEncyclopedia", "myPet", "dragonTreasure", "greenhouseFertilizer"})
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myInventory", "marketItem", "itemEncyclopedia", "myPet", "dragonTreasure", "greenhouseFertilizer"})
     */
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
