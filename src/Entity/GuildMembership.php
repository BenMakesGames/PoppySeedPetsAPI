<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GuildMembershipRepository")
 */
class GuildMembership
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", inversedBy="guildMembership", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Guild")
     * @ORM\JoinColumn(nullable=false)
     */
    private $guild;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petGuild", "petPublicProfile"})
     */
    private $joinedOn;

    /**
     * @ORM\Column(type="integer")
     */
    private $reputation = 0;

    /**
     * @ORM\Column(type="integer")
     */
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

        return $this;
    }

    public function getJoinedOn(): ?\DateTimeImmutable
    {
        return $this->joinedOn;
    }

    public function setJoinedOn(\DateTimeImmutable $joinedOn): self
    {
        $this->joinedOn = $joinedOn;

        return $this;
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

    /**
     * @Groups({"petGuild", "petPublicProfile"})
     */
    public function getRank(): string
    {
        $title = (int)($this->getLevel() / 10);
        $rank = ($this->getLevel() % 10) + 1;

        $titles = $this->getGuild()->getTitles();

        if($title >= count($titles))
            return 'Master';
        else
            return $titles[$title] . ' ' . $rank;
    }

    /**
     * @Groups({"petGuild", "petPublicProfile"})
     */
    public function getGuildName(): string
    {
        return $this->getGuild()->getName();
    }

    /**
     * @Groups({"petGuild", "petPublicProfile"})
     */
    public function getGuildEmblem(): string
    {
        return $this->getGuild()->getEmblem();
    }
}
