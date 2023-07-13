<?php

namespace App\Entity;

use App\Repository\WallpaperRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=WallpaperRepository::class)
 */
class Wallpaper
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"userPublicProfile", "petPublicProfile"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"userPublicProfile", "petPublicProfile"})
     */
    private $width;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"userPublicProfile", "petPublicProfile"})
     */
    private $height;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"userPublicProfile", "petPublicProfile"})
     */
    private $repeatXY;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(string $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getRepeatXY(): ?string
    {
        return $this->repeatXY;
    }

    public function setRepeatXY(string $repeatXY): self
    {
        $this->repeatXY = $repeatXY;

        return $this;
    }
}
