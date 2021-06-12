<?php

namespace App\Entity;

use App\Repository\UserUnlockedAuraRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserUnlockedAuraRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user_id_aura_id_idx", columns={"user_id", "aura_id"})
 *     }
 * )
 */
class UserUnlockedAura
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"myAura"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="unlockedAuras")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Enchantment::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myAura"})
     */
    private $aura;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"myAura"})
     */
    private $unlockedOn;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myAura"})
     */
    private $comment;

    public function __construct()
    {
        $this->unlockedOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAura(): Enchantment
    {
        return $this->aura;
    }

    public function setAura(Enchantment $aura): self
    {
        $this->aura = $aura;

        return $this;
    }

    public function getUnlockedOn(): \DateTimeImmutable
    {
        return $this->unlockedOn;
    }

    public function setUnlockedOn(\DateTimeImmutable $unlockedOn): self
    {
        $this->unlockedOn = $unlockedOn;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
