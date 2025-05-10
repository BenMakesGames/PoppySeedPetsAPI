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

use App\Enum\UserLinkVisibilityEnum;
use App\Enum\UserLinkWebsiteEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 40)]
    private $website;

    #[ORM\Column(type: 'string', length: 100)]
    private $nameOrId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 20)]
    private $visibility;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        if(!UserLinkWebsiteEnum::isAValue($website)) throw new \InvalidArgumentException();

        $this->website = $website;

        return $this;
    }

    public function getNameOrId(): string
    {
        return $this->nameOrId;
    }

    public function setNameOrId(string $nameOrId): self
    {
        $this->nameOrId = $nameOrId;

        return $this;
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

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        if(!UserLinkVisibilityEnum::isAValue($visibility)) throw new \InvalidArgumentException();

        $this->visibility = $visibility;

        return $this;
    }
}
