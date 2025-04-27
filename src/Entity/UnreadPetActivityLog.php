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

#[ORM\Entity]
class UnreadPetActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: PetActivityLogPet::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PetActivityLogPet $petActivityLog;

    public function __construct(PetActivityLogPet $petActivityLog)
    {
        $this->petActivityLog = $petActivityLog;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPetActivityLog(): ?PetActivityLogPet
    {
        return $this->petActivityLog;
    }

    public function setPetActivityLog(PetActivityLogPet $petActivityLog): self
    {
        $this->petActivityLog = $petActivityLog;

        return $this;
    }
}
