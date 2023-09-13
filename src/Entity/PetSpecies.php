<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="name_sort_idx", columns={"name_sort"}),
 *     @ORM\Index(name="family_idx", columns={"family"}),
 * })
 */
class PetSpecies
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"petEncyclopedia", "zoologistCatalog"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"myPet", "petEncyclopedia", "petShelterPet", "zoologistCatalog"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "userPublicProfile", "petEncyclopedia", "petPublicProfile", "petShelterPet", "parkEvent", "petFriend", "hollowEarth", "petGroupDetails", "guildMember", "petActivityLogAndPublicPet", "helperPet", "zoologistCatalog"})
     */
    private $image;

    /**
     * @ORM\Column(type="text")
     * @Groups({"petEncyclopedia"})
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet"})
     */
    private $handX;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet"})
     */
    private $handY;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet"})
     */
    private $handAngle;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petEncyclopedia", "petFriend", "petGroupDetails", "helperPet"})
     */
    private $flipX;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "helperPet"})
     */
    private $handBehind;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"myPet", "userPublicProfile", "petEncyclopedia"})
     */
    private $availableFromPetShelter;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "petEncyclopedia", "petFriend", "petGroupDetails", "parkEvent", "helperPet"})
     */
    private $pregnancyStyle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "petEncyclopedia", "petFriend", "petGroupDetails", "parkEvent", "helperPet"})
     */
    private $eggImage;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent", "helperPet"})
     */
    private $hatX;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent", "helperPet"})
     */
    private $hatY;

    /**
     * @ORM\Column(type="float")
     * @Groups({"myPet", "userPublicProfile", "petPublicProfile", "hollowEarth", "petGroupDetails", "parkEvent", "helperPet"})
     */
    private $hatAngle;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"petEncyclopedia"})
     */
    private $availableFromBreeding;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"zoologistCatalog"})
     */
    private $sheds;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myPet", "petEncyclopedia", "zoologistCatalog"})
     */
    private $family;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $nameSort;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pet", mappedBy="species")
     */
    private $pets;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"petEncyclopedia"})
     */
    private $physicalDescription;

    /**
     * @ORM\Column(type="text")
     * @Groups({"zoologistCatalog"})
     */
    private $zoologistThoughts;

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

    /**
     * @Groups({"petEncyclopedia"})
     */
    public function getAvailableAtSignup(): bool
    {
        return $this->getId() <= 16 || $this->getId() === 96 || $this->getId() === 100;
    }

    public function getSheds(): ?Item
    {
        return $this->sheds;
    }

    public function setSheds(?Item $sheds): self
    {
        $this->sheds = $sheds;

        return $this;
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

    public function getZoologistThoughts(): ?string
    {
        return $this->zoologistThoughts;
    }

    public function setZoologistThoughts(string $zoologistThoughts): self
    {
        $this->zoologistThoughts = $zoologistThoughts;

        return $this;
    }
}
