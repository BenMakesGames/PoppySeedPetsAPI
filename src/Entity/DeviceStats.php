<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\Index(name: 'time_idx', columns: ['time'])]
#[ORM\Entity]
class DeviceStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'datetime_immutable')]
    private $time;

    #[ORM\Column(type: 'string', length: 255)]
    private $userAgent;

    #[ORM\Column(type: 'string', length: 10)]
    private $language;

    #[ORM\Column(type: 'integer')]
    private $touchPoints;

    #[ORM\Column(type: 'integer')]
    private $windowWidth;

    #[ORM\Column(type: 'integer')]
    private $screenWidth;

    public function __construct()
    {
        $this->time = new \DateTimeImmutable();
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

    public function getTime(): \DateTimeImmutable
    {
        return $this->time;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getTouchPoints(): ?int
    {
        return $this->touchPoints;
    }

    public function setTouchPoints(int $touchPoints): self
    {
        $this->touchPoints = $touchPoints;

        return $this;
    }

    public function getWindowWidth(): ?int
    {
        return $this->windowWidth;
    }

    public function setWindowWidth(int $windowWidth): self
    {
        $this->windowWidth = $windowWidth;

        return $this;
    }

    public function getScreenWidth(): ?int
    {
        return $this->screenWidth;
    }

    public function setScreenWidth(int $screenWidth): self
    {
        $this->screenWidth = $screenWidth;

        return $this;
    }
}
