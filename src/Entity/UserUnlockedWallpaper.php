<?php

namespace App\Entity;

use App\Repository\UserUnlockedWallpaperRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserUnlockedWallpaperRepository::class)
 */
class UserUnlockedWallpaper
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Wallpaper::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $wallpaper;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $unlockedOn;

    public function __construct()
    {
        $this->unlockedOn = new \DateTimeImmutable();
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

    public function getWallpaper(): ?Wallpaper
    {
        return $this->wallpaper;
    }

    public function setWallpaper(?Wallpaper $wallpaper): self
    {
        $this->wallpaper = $wallpaper;

        return $this;
    }

    public function getUnlockedOn(): ?\DateTimeImmutable
    {
        return $this->unlockedOn;
    }
}
