<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class PetActivityLogTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\Column(type: 'string', length: 40, unique: true)]
    private $title;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\Column(type: 'string', length: 6)]
    private $color;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    #[ORM\Column(type: 'string', length: 12)]
    private $emoji;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getEmoji(): string
    {
        return $this->emoji;
    }
}
