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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "hollowEarth", "petGroupDetails"})
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "hollowEarth", "petGroupDetails"})
     */
    private $isSuffix;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ItemTool", inversedBy="enchantment", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myInventory", "myPet", "userPublicProfile", "petPublicProfile", "itemEncyclopedia", "hollowEarth", "petGroupDetails"})
     */
    private $effects;

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
}
