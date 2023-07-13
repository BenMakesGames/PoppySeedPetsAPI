<?php

namespace App\Entity;

use App\Repository\UserSelectedWallpaperRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserSelectedWallpaperRepository::class)
 */
class UserSelectedWallpaper
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="selectedWallpaper", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Wallpaper::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"userPublicProfile", "petPublicProfile"})
     */
    private $wallpaper;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"userPublicProfile", "petPublicProfile"})
     */
    private $colorA;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"userPublicProfile", "petPublicProfile"})
     */
    private $colorB;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
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

    public function getColorA(): ?string
    {
        return $this->colorA;
    }

    public function setColorA(string $colorA): self
    {
        $this->colorA = $colorA;

        return $this;
    }

    public function getColorB(): ?string
    {
        return $this->colorB;
    }

    public function setColorB(string $colorB): self
    {
        $this->colorB = $colorB;

        return $this;
    }
}
