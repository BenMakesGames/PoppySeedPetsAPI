<?php

namespace App\Entity;

use App\Enum\PetPregnancyStyleEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetBabyRepository")
 */
class PetBaby
{
    public const EGG_INCUBATION_TIME = 5760;
    public const PREGNANCY_DURATION = 30240;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $growth = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $affection = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PetSpecies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $species;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", mappedBy="pregnancy")
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet")
     * @ORM\JoinColumn(nullable=false)
     */
    private $otherParent;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $colorB;

    public function __construct()
    {
        $this->parents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrowth(): ?int
    {
        return $this->growth;
    }

    public function increaseGrowth(int $growth): self
    {
        $this->growth += $growth;

        return $this;
    }

    public function getAffection(): ?int
    {
        return $this->affection;
    }

    public function increaseAffection(int $affection): self
    {
        $this->affection += $affection;

        return $this;
    }

    public function getSpecies(): ?PetSpecies
    {
        return $this->species;
    }

    public function setSpecies(?PetSpecies $species): self
    {
        $this->species = $species;

        return $this;
    }

    public function getParent(): ?Pet
    {
        return $this->parent;
    }

    public function setParent(?Pet $parent): self
    {
        $this->parent = $parent;

        // set (or unset) the owning side of the relation if necessary
        $newPregnancy = $parent === null ? null : $this;
        if ($newPregnancy !== $parent->getPregnancy()) {
            $parent->setPregnancy($newPregnancy);
        }

        return $this;
    }

    public function getOtherParent(): ?Pet
    {
        return $this->otherParent;
    }

    public function setOtherParent(Pet $otherParent): self
    {
        $this->otherParent = $otherParent;

        return $this;
    }

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $colorA): self
    {
        $this->colorA = $colorA;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $colorB): self
    {
        $this->colorB = $colorB;

        return $this;
    }

    /**
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "petShelterPet", "petFriend"})
     */
    public function getEggColor(): ?string
    {
        // we only see the color expressed in an egg
        if($this->getParent()->getSpecies()->getPregnancyStyle() === PetPregnancyStyleEnum::EGG && $this->getGrowth() > self::EGG_INCUBATION_TIME)
            return $this->getColorA();
        else
            return null;
    }
}
