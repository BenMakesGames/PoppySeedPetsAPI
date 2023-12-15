<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'level_idx', columns: ['level'])]
#[ORM\Index(name: 'joined_on_idx', columns: ['joined_on'])]
#[ORM\Entity(repositoryClass: 'App\Repository\GuildMembershipRepository')]
class GuildMembership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: Pet::class, inversedBy: 'guildMembership', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $pet;

    #[Groups(["petGuild", "petPublicProfile"])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Guild')]
    #[ORM\JoinColumn(nullable: false)]
    private $guild;

    #[Groups(["petGuild", "petPublicProfile", "guildMember"])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $joinedOn;

    #[ORM\Column(type: 'integer')]
    private $reputation = 0;

    #[ORM\Column(type: 'integer')]
    private $level = 0;

    public function __construct()
    {
        $this->joinedOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getGuild(): ?Guild
    {
        return $this->guild;
    }

    public function setGuild(?Guild $guild): self
    {
        $this->guild = $guild;
        $this->joinedOn = new \DateTimeImmutable();
        $this->reputation = 0;
        $this->level = 0;

        return $this;
    }

    public function getJoinedOn(): ?\DateTimeImmutable
    {
        return $this->joinedOn;
    }

    public function getReputation(): int
    {
        return $this->reputation;
    }

    public function increaseReputation(): self
    {
        if($this->reputation >= $this->getReputationToLevel() - 1)
        {
            $this->reputation = 0;
            $this->level++;
        }
        else
            $this->reputation++;


        return $this;
    }

    public function getReputationToLevel(): int
    {
        return $this->level + 3;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getTitle(): int
    {
        return (int)($this->getLevel() / 10);
    }

    #[Groups(["petGuild", "petPublicProfile", "guildMember"])]
    public function getRank(): string
    {
        $title = $this->getTitle();
        $rank = ($this->getLevel() % 10) + 1;

        return match ($title)
        {
            0 => $this->getGuild()->getJuniorTitle() . ' ' . $rank,
            1 => $this->getGuild()->getMemberTitle() . ' ' . $rank,
            2 => $this->getGuild()->getSeniorTitle() . ' ' . $rank,
            default => $this->getGuild()->getMasterTitle(),
        };
    }
}
