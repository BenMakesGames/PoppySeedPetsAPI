<?php

namespace App\Entity;

use App\Repository\HollowEarthTileCardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=HollowEarthTileCardRepository::class)
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="name_idx", columns={"name"})
 *    }
 * )
 */
class HollowEarthTileCard
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    private $name;

    /**
     * @ORM\Column(type="json")
     */
    private $event = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $requiredAction = 0;

    /**
     * @ORM\ManyToOne(targetEntity=HollowEarthTileType::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"myInventory", "itemEncyclopedia"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEvent(): array
    {
        return $this->event;
    }

    public function setEvent(array $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getRequiredAction(): int
    {
        return $this->requiredAction;
    }

    public function setRequiredAction(int $requiredAction): self
    {
        $this->requiredAction = $requiredAction;

        return $this;
    }

    public function getType(): HollowEarthTileType
    {
        return $this->type;
    }

    public function setType(HollowEarthTileType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
