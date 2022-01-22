<?php

namespace App\Entity;

use App\Repository\PetActivityLogTagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PetActivityLogTagRepository::class)
 */
class PetActivityLogTag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=12)
     * @Groups({"petActivityLogs", "petActivityLogAndPublicPet"})
     */
    private $emoji;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(string $emoji): self
    {
        $this->emoji = $emoji;

        return $this;
    }
}
