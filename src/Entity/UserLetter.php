<?php

namespace App\Entity;

use App\Repository\UserLetterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserLetterRepository::class)]
class UserLetter
{
    /**
     * @Groups({"myLetters"})
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Groups({"myLetters"})
     */
    #[ORM\ManyToOne(targetEntity: Letter::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $letter;

    /**
     * @Groups({"myLetters"})
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $receivedOn;

    /**
     * @Groups({"myLetters"})
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $comment;

    /**
     * @Groups({"myLetters"})
     */
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
