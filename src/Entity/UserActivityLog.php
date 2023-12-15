<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class UserActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[Groups(["userActivityLogs"])]
    #[ORM\Column(type: 'text')]
    private $entry;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdOn;

    #[Groups(["userActivityLogs"])]
    #[ORM\ManyToMany(targetEntity: UserActivityLogTag::class)]
    private $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->createdOn = new \DateTimeImmutable();
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

    public function getEntry(): ?string
    {
        return $this->entry;
    }

    public function setEntry(string $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeImmutable $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return Collection<int, UserActivityLogTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param UserActivityLogTag[] $tags
     */
    public function addTags(array $tags): self
    {
        foreach($tags as $tag)
            $this->addTag($tag);

        return $this;
    }

    public function addTag(UserActivityLogTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(UserActivityLogTag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
