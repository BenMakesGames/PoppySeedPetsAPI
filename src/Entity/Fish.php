<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FishRepository")
 */
class Fish
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
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $difficulty;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $loot;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $additionalStat;

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

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getLoot(): ?string
    {
        return $this->loot;
    }

    public function setLoot(string $loot): self
    {
        $this->loot = $loot;

        return $this;
    }

    public function getAdditionalStat(): ?string
    {
        return $this->additionalStat;
    }

    public function setAdditionalStat(string $additionalStat): self
    {
        $this->additionalStat = $additionalStat;

        return $this;
    }
}
