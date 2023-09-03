<?php

namespace App\Entity;

use App\Repository\UserFieldGuideEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserFieldGuideEntryRepository::class)
 */
class UserFieldGuideEntry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="fieldGuideEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=FieldGuideEntry::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({ "myFieldGuide" })
     */
    private $entry;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({ "myFieldGuide" })
     */
    private $discoveredOn;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({ "myFieldGuide" })
     */
    private $comment;

    public function __construct()
    {
        $this->discoveredOn = new \DateTimeImmutable();
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

    public function getEntry(): ?FieldGuideEntry
    {
        return $this->entry;
    }

    public function setEntry(?FieldGuideEntry $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getDiscoveredOn(): ?\DateTimeImmutable
    {
        return $this->discoveredOn;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
