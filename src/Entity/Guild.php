<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Guild
{
    #[Groups(["guildEncyclopedia", "petGuild", "petPublicProfile"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["guildEncyclopedia", "petGuild", "petPublicProfile"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[Groups(["guildEncyclopedia", "petGuild", "petPublicProfile"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $emblem;

    #[Groups(["guildEncyclopedia"])]
    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $starterTool;

    #[Groups(["guildEncyclopedia"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $quote;

    #[ORM\Column(type: 'string', length: 20)]
    private $juniorTitle;

    #[ORM\Column(type: 'string', length: 20)]
    private $memberTitle;

    #[ORM\Column(type: 'string', length: 20)]
    private $seniorTitle;

    #[ORM\Column(type: 'string', length: 20)]
    private $masterTitle;

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

    public function getJuniorTitle(): string
    {
        return $this->juniorTitle;
    }

    public function setJuniorTitle(string $juniorTitle): self
    {
        $this->juniorTitle = $juniorTitle;

        return $this;
    }

    public function getMemberTitle(): string
    {
        return $this->memberTitle;
    }

    public function setMemberTitle(string $memberTitle): self
    {
        $this->memberTitle = $memberTitle;

        return $this;
    }

    public function getSeniorTitle(): string
    {
        return $this->seniorTitle;
    }

    public function setSeniorTitle(string $seniorTitle): self
    {
        $this->seniorTitle = $seniorTitle;

        return $this;
    }

    public function getMasterTitle(): string
    {
        return $this->masterTitle;
    }

    public function setMasterTitle(string $masterTitle): self
    {
        $this->masterTitle = $masterTitle;

        return $this;
    }
}
