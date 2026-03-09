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
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Table]
#[ORM\Entity]
class Vault
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'vault', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $openUntil;

    /** @noinspection PhpUnusedPrivateFieldInspection */
    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore property.unused */
    private int $version;

    public function __construct(User $user)
    {
        $this->id = new Ulid();
        $this->user = $user;
        $this->openUntil = new \DateTimeImmutable();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getOpenUntil(): \DateTimeImmutable
    {
        return $this->openUntil;
    }

    public function setOpenUntil(\DateTimeImmutable $openUntil): self
    {
        $this->openUntil = $openUntil;

        return $this;
    }

    public function isOpen(): bool
    {
        return $this->openUntil > new \DateTimeImmutable();
    }
}
