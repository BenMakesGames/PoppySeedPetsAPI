<?php
declare(strict_types=1);

namespace App\Entity;

use App\Enum\UserLinkVisibilityEnum;
use App\Enum\UserLinkWebsiteEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 40)]
    private $website;

    #[ORM\Column(type: 'string', length: 100)]
    private $nameOrId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 20)]
    private $visibility;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        if(!UserLinkWebsiteEnum::isAValue($website)) throw new \InvalidArgumentException();

        $this->website = $website;

        return $this;
    }

    public function getNameOrId(): string
    {
        return $this->nameOrId;
    }

    public function setNameOrId(string $nameOrId): self
    {
        $this->nameOrId = $nameOrId;

        return $this;
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

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        if(!UserLinkVisibilityEnum::isAValue($visibility)) throw new \InvalidArgumentException();

        $this->visibility = $visibility;

        return $this;
    }
}
