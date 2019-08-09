<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemPlantRepository")
 */
class ItemPlant
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
    private $timeToGrow;

    /**
     * @ORM\Column(type="integer")
     */
    private $yield;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"greenhousePlant"})
     */
    private $sproutImage;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"greenhousePlant"})
     */
    private $mediumImage;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"greenhousePlant"})
     */
    private $adultImage;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"greenhousePlant"})
     */
    private $harvestableImage;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Item", mappedBy="plant", cascade={"persist", "remove"})
     * @Groups({"greenhousePlant"})
     */
    private $item;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimeToGrow(): ?int
    {
        return $this->timeToGrow;
    }

    public function setTimeToGrow(int $timeToGrow): self
    {
        $this->timeToGrow = $timeToGrow;

        return $this;
    }

    public function getYield(): ?int
    {
        return $this->yield;
    }

    public function setYield(int $yield): self
    {
        $this->yield = $yield;

        return $this;
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
}
