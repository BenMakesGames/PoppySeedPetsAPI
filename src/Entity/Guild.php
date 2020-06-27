<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GuildRepository")
 */
class Guild
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"guildEncyclopedia", "petGuild", "petPublicProfile"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"guildEncyclopedia", "petGuild", "petPublicProfile"})
     */
    private $name;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $titles = [];

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"guildEncyclopedia", "petGuild", "petPublicProfile"})
     */
    private $emblem;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"guildEncyclopedia"})
     */
    private $starterTool;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"guildEncyclopedia"})
     */
    private $quote;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTitles(): ?array
    {
        return $this->titles;
    }

    public function setTitles(array $titles): self
    {
        $this->titles = $titles;

        return $this;
    }

    public function getEmblem(): ?string
    {
        return $this->emblem;
    }

    public function setEmblem(string $emblem): self
    {
        $this->emblem = $emblem;

        return $this;
    }

    public function getStarterTool(): ?Item
    {
        return $this->starterTool;
    }

    public function setStarterTool(?Item $starterTool): self
    {
        $this->starterTool = $starterTool;

        return $this;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(string $quote): self
    {
        $this->quote = $quote;

        return $this;
    }
}
