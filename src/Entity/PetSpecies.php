<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetSpeciesRepository")
 */
class PetSpecies
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"petEncyclopedia", "petShelterPet"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "userPublicProfile", "petEncyclopedia", "petPublicProfile", "petShelterPet", "parkEvent", "petFriend", "hollowEarth"})
     */
    private $image;

    /**
     * @ORM\Column(type="text")
     * @Groups({"petEncyclopedia"})
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $handX;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $handY;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $handAngle;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $handFlipX;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth"})
     */
    private $handBehind;

    /**
     * @ORM\Column(type="boolean")
     */
    private $availableFromPetShelter;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getHandX(): ?float
    {
        return $this->handX;
    }

    public function setHandX(float $handX): self
    {
        $this->handX = $handX;

        return $this;
    }

    public function getHandY(): ?float
    {
        return $this->handY;
    }

    public function setHandY(float $handY): self
    {
        $this->handY = $handY;

        return $this;
    }

    public function getHandAngle(): ?float
    {
        return $this->handAngle;
    }

    public function setHandAngle(float $handAngle): self
    {
        $this->handAngle = $handAngle;

        return $this;
    }

    public function getHandFlipX(): ?bool
    {
        return $this->handFlipX;
    }

    public function setHandFlipX(bool $handFlipX): self
    {
        $this->handFlipX = $handFlipX;

        return $this;
    }

    public function getHandBehind(): ?bool
    {
        return $this->handBehind;
    }

    public function setHandBehind(bool $hand_behind): self
    {
        $this->handBehind = $hand_behind;

        return $this;
    }

    public function getAvailableFromPetShelter(): ?bool
    {
        return $this->availableFromPetShelter;
    }

    public function setAvailableFromPetShelter(bool $availableFromPetShelter): self
    {
        $this->availableFromPetShelter = $availableFromPetShelter;

        return $this;
    }
}
