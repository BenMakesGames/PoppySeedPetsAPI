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
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class PetActivityLogTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\Column(type: 'string', length: 40, unique: true)]
    private string $title;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\Column(type: 'string', length: 6)]
    private string $color;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\Column(type: 'string', length: 100)]
    private string $emoji;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getEmoji(): string
    {
        return $this->emoji;
    }


}
