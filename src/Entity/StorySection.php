<?php

namespace App\Entity;

use App\Enum\StorySectionStyleEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StorySection
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $style;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $background;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="json")
     */
    private $choices = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Story")
     * @ORM\JoinColumn(nullable=false)
     */
    private $story;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function setStyle(string $style): self
    {
        if(!StorySectionStyleEnum::isAValue($style))
            throw new \InvalidArgumentException('$style must be a valid StorySectionStyleEnum value.');

        $this->style = $style;

        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background): self
    {
        $this->background = $background;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getChoices(): ?array
    {
        return $this->choices;
    }

    public function setChoices(array $choices): self
    {
        $this->choices = $choices;

        return $this;
    }

    public function getStory(): Story
    {
        return $this->story;
    }

    public function setStory(Story $story): self
    {
        $this->story = $story;

        return $this;
    }
}
