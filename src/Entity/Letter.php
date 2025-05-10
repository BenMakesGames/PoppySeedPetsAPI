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

use App\Repository\LetterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'sender_idx', columns: ['sender'])]
#[ORM\Entity(repositoryClass: LetterRepository::class)]
class Letter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[Groups(["myLetters"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $sender;

    #[Groups(["myLetters"])]
    #[ORM\Column(type: 'text')]
    private $body;

    #[Groups(["myLetters"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[Groups(["myLetters"])]
    #[ORM\ManyToOne(targetEntity: Item::class)]
    private $attachment;

    #[ORM\ManyToOne(targetEntity: Enchantment::class)]
    private $bonus;

    #[ORM\ManyToOne(targetEntity: Spice::class)]
    private $spice;

    #[ORM\ManyToOne(targetEntity: FieldGuideEntry::class)]
    private $fieldGuideEntry;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAttachment(): ?Item
    {
        return $this->attachment;
    }

    public function setAttachment(?Item $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getBonus(): ?Enchantment
    {
        return $this->bonus;
    }

    public function setBonus(?Enchantment $bonus): self
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getSpice(): ?Spice
    {
        return $this->spice;
    }

    public function setSpice(?Spice $spice): self
    {
        $this->spice = $spice;

        return $this;
    }

    public function getFieldGuideEntry(): ?FieldGuideEntry
    {
        return $this->fieldGuideEntry;
    }

    public function setFieldGuideEntry(?FieldGuideEntry $fieldGuideEntry): self
    {
        $this->fieldGuideEntry = $fieldGuideEntry;

        return $this;
    }
}
