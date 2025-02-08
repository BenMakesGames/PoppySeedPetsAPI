<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class ItemTreasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["dragonTreasure"])]
    #[ORM\Column(type: 'integer')]
    private $silver;

    #[Groups(["dragonTreasure"])]
    #[ORM\Column(type: 'integer')]
    private $gold;

    #[Groups(["dragonTreasure"])]
    #[ORM\Column(type: 'integer')]
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
