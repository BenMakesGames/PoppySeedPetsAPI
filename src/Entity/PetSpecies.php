<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'name_sort_idx', columns: ['name_sort'])]
#[ORM\Index(name: 'family_idx', columns: ['family'])]
#[ORM\Entity]
class PetSpecies
{
    #[Groups(["petEncyclopedia", "zoologistCatalog", "typeahead"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["myPet", "petEncyclopedia", "petShelterPet", "zoologistCatalog", "typeahead"])]
    #[ORM\Column(type: 'string', length: 40, unique: true)]
    private $name;

    #[Groups(["myPet", "userPublicProfile", "petEncyclopedia", "petPublicProfile", "petShelterPet", "parkEvent", "petFriend", "hollowEarth", "petGroupDetails", "guildMember", "petActivityLogAndPublicPet", "helperPet", "zoologistCatalog", "typeahead", 'petActivityLogs'])]
    #[ORM\Column(type: 'string', length: 40)]
    private $image;

    #[Groups(["petEncyclopedia"])]
    #[ORM\Column(type: 'text')]
    private $description;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'float')]
    private $handX;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'float')]
    private $handY;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'float')]
    private $handAngle;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petEncyclopedia", "petFriend", "petGroupDetails", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'boolean')]
    private $flipX;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'boolean')]
    private $handBehind;

    #[Groups(["myPet", "userPublicProfile", "petEncyclopedia"])]
    #[ORM\Column(type: 'boolean')]
    private $availableFromPetShelter;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "petEncyclopedia", "petFriend", "petGroupDetails", "parkEvent", "helperPet"])]
    #[ORM\Column(type: 'integer')]
    private $pregnancyStyle;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "petEncyclopedia", "petFriend", "petGroupDetails", "parkEvent", "helperPet"])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $eggImage;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'float')]
    private $hatX;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'float')]
    private $hatY;

    #[Groups(["myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent", "helperPet", "petActivityLogAndPublicPet", 'petActivityLogs'])]
    #[ORM\Column(type: 'float')]
    private $hatAngle;

    #[Groups(["petEncyclopedia"])]
    #[ORM\Column(type: 'boolean')]
    private $availableFromBreeding;

    #[Groups(["zoologistCatalog"])]
    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $sheds;

    #[Groups(["myPet", "petEncyclopedia", "zoologistCatalog"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $family;

    #[ORM\Column(type: 'string', length: 40)]
    private $nameSort;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Pet', mappedBy: 'species')]
    private $pets;

    #[Groups(["petEncyclopedia"])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $physicalDescription;

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

    public function getFlipX(): ?bool
    {
        return $this->flipX;
    }

    public function setFlipX(bool $flipX): self
    {
        $this->flipX = $flipX;

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

    public function getPregnancyStyle(): ?int
    {
        return $this->pregnancyStyle;
    }

    public function setPregnancyStyle(int $pregnancyStyle): self
    {
        $this->pregnancyStyle = $pregnancyStyle;

        return $this;
    }

    public function getEggImage(): ?string
    {
        return $this->eggImage;
    }

    public function setEggImage(?string $eggImage): self
    {
        $this->eggImage = $eggImage;

        return $this;
    }

    public function getHatX(): ?float
    {
        return $this->hatX;
    }

    public function setHatX(float $hatX): self
    {
        $this->hatX = $hatX;

        return $this;
    }

    public function getHatY(): ?float
    {
        return $this->hatY;
    }

    public function setHatY(float $hatY): self
    {
        $this->hatY = $hatY;

        return $this;
    }

    public function getHatAngle(): ?float
    {
        return $this->hatAngle;
    }

    public function setHatAngle(float $hatAngle): self
    {
        $this->hatAngle = $hatAngle;

        return $this;
    }

    public function getAvailableFromBreeding(): ?bool
    {
        return $this->availableFromBreeding;
    }

    public function setAvailableFromBreeding(bool $availableFromBreeding): self
    {
        $this->availableFromBreeding = $availableFromBreeding;

        return $this;
    }

    #[Groups(["petEncyclopedia"])]
    public function getAvailableAtSignup(): bool
    {
        return $this->getId() <= 16 || $this->getId() === 96 || $this->getId() === 100;
    }

    public function getSheds(): Item
    {
        return $this->sheds;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(string $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getNameSort(): ?string
    {
        return $this->nameSort;
    }

    public function setNameSort(string $nameSort): self
    {
        $this->nameSort = $nameSort;

        return $this;
    }

    public function getPhysicalDescription(): ?string
    {
        return $this->physicalDescription;
    }

    public function setPhysicalDescription(?string $physicalDescription): self
    {
        $this->physicalDescription = $physicalDescription;

        return $this;
    }
}
