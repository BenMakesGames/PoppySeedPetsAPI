<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class MuseumItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Groups({"museum"})
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Groups({"museum"})
     */
    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'museumDonations')]
    #[ORM\JoinColumn(nullable: false)]
    private $item;

    /**
     * @Groups({"museum"})
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $donatedOn;

    /**
     * @Groups({"museum"})
     */
    #[ORM\Column(type: 'json')]
    private $comments = [];

    /**
     * @Groups({"museum"})
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
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
