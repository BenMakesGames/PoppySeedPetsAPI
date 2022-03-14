<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HollowEarthTileRepository")
 */
class HollowEarthTile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"hollowEarth"})
     */
    private $x;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"hollowEarth"})
     */
    private $y;

    /**
     * @ORM\Column(type="string", length=1)
     * @Groups({"hollowEarth"})
     */
    private $moveDirection;

    /**
     * @ORM\ManyToMany(targetEntity=HollowEarthTileType::class)
     */
    private $types;

    /**
     * @ORM\ManyToOne(targetEntity=HollowEarthTileCard::class)
     */
    private $card;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"hollowEarth"})
     */
    private $goodsSide;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $goods = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isTradingDepot;

    public function __construct()
    {
        $this->types = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(int $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(int $y): self
    {
        $this->y = $y;

        return $this;
    }

    public function getMoveDirection(): string
    {
        return $this->moveDirection;
    }

    public function setMoveDirection(string $moveDirection): self
    {
        $this->moveDirection = $moveDirection;

        return $this;
    }

    /**
     * @return Collection|HollowEarthTileType[]
     */
    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function addType(HollowEarthTileType $type): self
    {
        if (!$this->types->contains($type)) {
            $this->types[] = $type;
        }

        return $this;
    }

    public function removeType(HollowEarthTileType $type): self
    {
        $this->types->removeElement($type);

        return $this;
    }

    public function getCard(): ?HollowEarthTileCard
    {
        return $this->card;
    }

    public function setCard(?HollowEarthTileCard $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function getGoodsSide(): ?string
    {
        return $this->goodsSide;
    }

    public function setGoodsSide(?string $goodsSide): self
    {
        $this->goodsSide = $goodsSide;

        return $this;
    }

    public function getGoods(): ?array
    {
        return $this->goods;
    }

    public function setGoods(?array $goods): self
    {
        $this->goods = $goods;

        return $this;
    }

    public function getIsTradingDepot(): ?bool
    {
        return $this->isTradingDepot;
    }

    public function setIsTradingDepot(bool $isTradingDepot): self
    {
        $this->isTradingDepot = $isTradingDepot;

        return $this;
    }
}
