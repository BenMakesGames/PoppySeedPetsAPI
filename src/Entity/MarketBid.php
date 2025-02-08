<?php
declare(strict_types=1);

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Repository\MarketBidRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MarketBidRepository::class)]
class MarketBid
{
    #[Groups(["myBids"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["myBids"])]
    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $item;

    #[Groups(["myBids"])]
    #[ORM\Column(type: 'integer')]
    private $bid;

    #[Groups(["myBids"])]
    #[ORM\Column(type: 'smallint')]
    private $quantity;

    #[Groups(["myBids"])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $createdOn;

    #[Groups(["myBids"])]
    #[ORM\Column(type: 'smallint')]
    private $targetLocation = 0;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getBid(): ?int
    {
        return $this->bid;
    }

    public function setBid(int $bid): self
    {
        $this->bid = $bid;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getTargetLocation(): int
    {
        return $this->targetLocation;
    }

    public function setTargetLocation(int $targetLocation): self
    {
        if(!LocationEnum::isAValue($targetLocation))
            throw new EnumInvalidValueException(LocationEnum::class, $targetLocation);

        $this->targetLocation = $targetLocation;

        return $this;
    }
}
