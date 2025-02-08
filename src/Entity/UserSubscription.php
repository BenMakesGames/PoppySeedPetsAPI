<?php
declare(strict_types=1);

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\PatreonTierEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class UserSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $updatedOn;

    #[ORM\Column(type: 'integer')]
    private $patreonUserId;

    #[Groups(["myAccount"])]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $tier;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'subscription', cascade: ['persist', 'remove'])]
    private $user;

    public function __construct()
    {
        $this->updatedOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedOn(): ?\DateTimeImmutable
    {
        return $this->updatedOn;
    }

    public function setUpdatedOn(): self
    {
        $this->updatedOn = new \DateTimeImmutable();

        return $this;
    }

    public function getPatreonUserId(): int
    {
        return $this->patreonUserId;
    }

    public function setPatreonUserId(int $patreonUserId): self
    {
        $this->patreonUserId = $patreonUserId;

        return $this;
    }

    public function getTier(): string
    {
        return $this->tier;
    }

    public function setTier(?string $tier): self
    {
        if($tier !== null && !PatreonTierEnum::isAValue($tier))
            throw new EnumInvalidValueException(PatreonTierEnum::class, $tier);

        $this->tier = $tier;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
