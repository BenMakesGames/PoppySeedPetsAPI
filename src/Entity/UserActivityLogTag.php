<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class UserActivityLogTag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Groups({"userActivityLogs"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=6)
     * @Groups({"userActivityLogs"})
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=12)
     * @Groups({"userActivityLogs"})
     */
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
