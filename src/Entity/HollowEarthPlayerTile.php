<?php

namespace App\Entity;

use App\Repository\HollowEarthPlayerTileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'player_id_tile_id_idx', columns: ['player_id', 'tile_id'])]
#[ORM\Entity(repositoryClass: HollowEarthPlayerTileRepository::class)]
class HollowEarthPlayerTile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $player;

    #[ORM\ManyToOne(targetEntity: HollowEarthTile::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $tile;

    #[ORM\ManyToOne(targetEntity: HollowEarthTileCard::class)]
    private $card;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $goods;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?User
    {
        return $this->player;
    }

    public function setPlayer(?User $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getTile(): ?HollowEarthTile
    {
        return $this->tile;
    }

    public function setTile(?HollowEarthTile $tile): self
    {
        $this->tile = $tile;

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

    public function getGoods(): ?string
    {
        return $this->goods;
    }

    public function setGoods(?string $goods): self
    {
        $this->goods = $goods;

        return $this;
    }
}
