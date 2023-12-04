<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\PlantYieldItemRepository')]
class PlantYieldItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\PlantYield', inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private $plantYield;

    #[ORM\Column(type: 'integer')]
    private $percentChance;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $item;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlantYield(): ?PlantYield
    {
        return $this->plantYield;
    }

    public function setPlantYield(?PlantYield $plantYield): self
    {
        $this->plantYield = $plantYield;

        return $this;
    }

    public function getPercentChance(): ?int
    {
        return $this->percentChance;
    }

    public function setPercentChance(int $percentChance): self
    {
        $this->percentChance = $percentChance;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }
}
