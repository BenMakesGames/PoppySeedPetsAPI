<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['article'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['article'])]
    private $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['article'])]
    private $body;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['article'])]
    private $createdOn;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['article'])]
    private $author;

    #[ORM\ManyToMany(targetEntity: DesignGoal::class, inversedBy: 'articles')]
    #[Groups(['article'])]
    private $designGoals;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['article'])]
    private $imageUrl;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
        $this->designGoals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|DesignGoal[]
     */
    public function getDesignGoals(): Collection
    {
        return $this->designGoals;
    }

    public function addDesignGoal(DesignGoal $designGoal): self
    {
        if (!$this->designGoals->contains($designGoal)) {
            $this->designGoals[] = $designGoal;
        }

        return $this;
    }

    public function removeDesignGoal(DesignGoal $designGoal): self
    {
        $this->designGoals->removeElement($designGoal);

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
}
