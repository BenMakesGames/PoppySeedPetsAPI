<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Table]
#[ORM\UniqueConstraint(name: 'name_idx', columns: ['name'])]
#[ORM\Entity]
class HollowEarthTileCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["myInventory", "itemEncyclopedia"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $name;

    #[ORM\Column(type: 'json')]
    private $event = [];

    #[ORM\Column(type: 'integer')]
    private $requiredAction = 0;

    #[Groups(["myInventory", "itemEncyclopedia"])]
    #[ORM\ManyToOne(targetEntity: HollowEarthTileType::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $type;

    #[ORM\Column(type: 'string', length: 40)]
    private $image;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $author = null;

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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }
}
