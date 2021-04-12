<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MuseumItemRepository")
 */
class MuseumItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"museum"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class, inversedBy="museumDonations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"museum"})
     */
    private $item;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"museum"})
     */
    private $donatedOn;

    /**
     * @ORM\Column(type="json")
     * @Groups({"museum"})
     */
    private $comments = [];

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @Groups({"museum"})
     */
    private $createdBy;

    public function __construct()
    {
        $this->donatedOn = new \DateTimeImmutable();
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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getDonatedOn(): ?\DateTimeImmutable
    {
        return $this->donatedOn;
    }

    public function getComments(): ?array
    {
        return $this->comments;
    }

    public function setComments(array $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getCreatedBy(): ?user
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?user $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
