<?php

namespace App\Entity;

use App\Repository\ItemTreasureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ItemTreasureRepository::class)
 */
class ItemTreasure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"dragonTreasure"})
     */
    private $silver;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"dragonTreasure"})
     */
    private $gold;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"dragonTreasure"})
     */
    private $gems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSilver(): ?int
    {
        return $this->silver;
    }

    public function setSilver(int $silver): self
    {
        $this->silver = $silver;

        return $this;
    }

    public function getGold(): ?int
    {
        return $this->gold;
    }

    public function setGold(int $gold): self
    {
        $this->gold = $gold;

        return $this;
    }

    public function getGems(): ?int
    {
        return $this->gems;
    }

    public function setGems(int $gems): self
    {
        $this->gems = $gems;

        return $this;
    }
}
