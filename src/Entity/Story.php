<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StoryRepository")
 */
class Story
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $title;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\StorySection", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $firstSection;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getFirstSection(): StorySection
    {
        return $this->firstSection;
    }

    public function setFirstSection(StorySection $firstSection): self
    {
        $this->firstSection = $firstSection;

        return $this;
    }
}
