<?php

namespace App\Entity;

use App\Repository\LetterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=LetterRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="sender_idx", columns={"sender"}),
 * })
 */
class Letter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myLetters"})
     */
    private $sender;

    /**
     * @ORM\Column(type="text")
     * @Groups({"myLetters"})
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"myLetters"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class)
     */
    private $attachment;

    /**
     * @ORM\ManyToOne(targetEntity=Enchantment::class)
     */
    private $bonus;

    /**
     * @ORM\ManyToOne(targetEntity=Spice::class)
     */
    private $spice;

    /**
     * @ORM\ManyToOne(targetEntity=FieldGuideEntry::class)
     */
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
