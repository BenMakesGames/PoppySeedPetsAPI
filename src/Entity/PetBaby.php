<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetBabyRepository")
 */
class PetBaby
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $growth;

    /**
     * @ORM\Column(type="integer")
     */
    private $affection;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PetSpecies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $species;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", mappedBy="pregnancy", cascade={"persist", "remove"})
     */
    private $parent;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $otherParent;

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

    public function setGrowth(int $growth): self
    {
        $this->growth = $growth;

        return $this;
    }

    public function getAffection(): ?int
    {
        return $this->affection;
    }

    public function setAffection(int $affection): self
    {
        $this->affection = $affection;

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
}
