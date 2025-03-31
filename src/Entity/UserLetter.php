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

#[ORM\Entity]
class UserLetter
{
    #[Groups(["myLetters"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["myLetters"])]
    #[ORM\ManyToOne(targetEntity: Letter::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $letter;

    #[Groups(["myLetters"])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $receivedOn;

    #[Groups(["myLetters"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $comment;

    #[Groups(["myLetters"])]
    #[ORM\Column(type: 'boolean')]
    private $isRead = false;

    public function __construct()
    {
        $this->receivedOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLetter(): Letter
    {
        return $this->letter;
    }

    public function setLetter(Letter $letter): self
    {
        $this->letter = $letter;

        return $this;
    }

    public function getReceivedOn(): ?\DateTimeImmutable
    {
        return $this->receivedOn;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(): self
    {
        $this->isRead = true;

        return $this;
    }
}
