<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\UserFollowingRepository')]
class UserFollowing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["myFollowers"])]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'following')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'followedBy')]
    #[ORM\JoinColumn(nullable: false)]
    private $following;

    #[ORM\Column(type: 'datetime_immutable')]
    private $dateAdded;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $note;

    public function __construct()
    {
        $this->dateAdded = new \DateTimeImmutable();
    }

    public function getId(): ?int
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

    public function getFollowing(): User
    {
        return $this->following;
    }

    public function setFollowing(User $user): self
    {
        $this->following = $user;

        return $this;
    }

    public function getDateAdded(): \DateTimeImmutable
    {
        return $this->dateAdded;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
