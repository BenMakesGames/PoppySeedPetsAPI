<?php

namespace App\Entity;

use App\Repository\HollowEarthPlayerTileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HollowEarthPlayerTileRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="player_id_tile_id_idx", columns={"player_id", "tile_id"})
 *     }
 * )
 */
class HollowEarthPlayerTile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity=HollowEarthTile::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tile;

    /**
     * @ORM\ManyToOne(targetEntity=HollowEarthTileCard::class)
     */
    private $card;

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
}
