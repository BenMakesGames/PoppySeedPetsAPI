<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlantRepository")
 */
class Plant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $sproutImage;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $mediumImage;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $adultImage;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $harvestableImage;

    /**
     * @ORM\OneToOne(targetEntity=Item::class, mappedBy="plant")
     */
    private $item;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeToAdult;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeToFruit;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"greenhousePlant"})
     */
    private $type = 'earth';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PlantYield", mappedBy="plant", orphanRemoval=true)
     */
    private $plantYields;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"greenhousePlant"})
     */
    private $name;

    public function __construct()
    {
        $this->plantYields = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSproutImage(): ?string
    {
        return $this->sproutImage;
    }

    public function setSproutImage(string $sproutImage): self
    {
        $this->sproutImage = $sproutImage;

        return $this;
    }

    public function getMediumImage(): ?string
    {
        return $this->mediumImage;
    }

    public function setMediumImage(string $mediumImage): self
    {
        $this->mediumImage = $mediumImage;

        return $this;
    }

    public function getAdultImage(): ?string
    {
        return $this->adultImage;
    }

    public function setAdultImage(string $adultImage): self
    {
        $this->adultImage = $adultImage;

        return $this;
    }

    public function getHarvestableImage(): ?string
    {
        return $this->harvestableImage;
    }

    public function setHarvestableImage(string $harvestableImage): self
    {
        $this->harvestableImage = $harvestableImage;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        // set (or unset) the owning side of the relation if necessary
        $newPlant = $item === null ? null : $this;
        if ($newPlant !== $item->getPlant()) {
            $item->setPlant($newPlant);
        }

        return $this;
    }

    public function getTimeToAdult(): ?int
    {
        return $this->timeToAdult;
    }

    public function setTimeToAdult(int $timeToAdult): self
    {
        $this->timeToAdult = $timeToAdult;

        return $this;
    }

    public function getTimeToFruit(): ?int
    {
        return $this->timeToFruit;
    }

    public function setTimeToFruit(int $timeToFruit): self
    {
        $this->timeToFruit = $timeToFruit;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|PlantYield[]
     */
    public function getPlantYields(): Collection
    {
        return $this->plantYields;
    }

    public function addPlantYield(PlantYield $plantYield): self
    {
        if (!$this->plantYields->contains($plantYield)) {
            $this->plantYields[] = $plantYield;
            $plantYield->setPlant($this);
        }

        return $this;
    }

    public function removePlantYield(PlantYield $plantYield): self
    {
        if ($this->plantYields->contains($plantYield)) {
            $this->plantYields->removeElement($plantYield);
            // set the owning side to null (unless already changed)
            if ($plantYield->getPlant() === $this) {
                $plantYield->setPlant(null);
            }
        }

        return $this;
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
}
