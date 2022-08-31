<?php

namespace App\Entity;

use App\Repository\MonthlyStoryAdventureStepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MonthlyStoryAdventureStepRepository::class)
 */
class MonthlyStoryAdventureStep
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({ "starKindredStoryStepAvailable" })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=MonthlyStoryAdventure::class, inversedBy="steps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adventure;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups({ "starKindredStoryStepAvailable" })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({ "starKindredStoryStepAvailable", "starKindredStoryStepComplete" })
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $step;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $previousStep;

    /**
     * @ORM\Column(type="float")
     * @Groups({ "starKindredStoryStepAvailable", "starKindredStoryStepComplete" })
     */
    private $x;

    /**
     * @ORM\Column(type="float")
     * @Groups({ "starKindredStoryStepAvailable", "starKindredStoryStepComplete" })
     */
    private $y;

    /**
     * @ORM\Column(type="integer")
     * @Groups({ "starKindredStoryStepAvailable" })
     */
    private $minPets;

    /**
     * @ORM\Column(type="integer")
     * @Groups({ "starKindredStoryStepAvailable" })
     */
    private $maxPets;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $narrative;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $treasure;

    /**
     * @ORM\ManyToOne(targetEntity=Aura::class)
     */
    private $aura;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({ "starKindredStoryStepAvailable" })
     */
    private $pinOverride;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdventure(): ?MonthlyStoryAdventure
    {
        return $this->adventure;
    }

    public function setAdventure(?MonthlyStoryAdventure $adventure): self
    {
        $this->adventure = $adventure;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(int $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getPreviousStep(): ?int
    {
        return $this->previousStep;
    }

    public function setPreviousStep(?int $previousStep): self
    {
        $this->previousStep = $previousStep;

        return $this;
    }

    public function getX(): ?float
    {
        return $this->x;
    }

    public function setX(float $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?float
    {
        return $this->y;
    }

    public function setY(float $y): self
    {
        $this->y = $y;

        return $this;
    }

    public function getMinPets(): ?int
    {
        return $this->minPets;
    }

    public function setMinPets(int $minPets): self
    {
        $this->minPets = $minPets;

        return $this;
    }

    public function getMaxPets(): ?int
    {
        return $this->maxPets;
    }

    public function setMaxPets(int $maxPets): self
    {
        $this->maxPets = $maxPets;

        return $this;
    }

    public function getNarrative(): ?string
    {
        return $this->narrative;
    }

    public function setNarrative(?string $narrative): self
    {
        $this->narrative = $narrative;

        return $this;
    }

    public function getTreasure(): ?string
    {
        return $this->treasure;
    }

    public function setTreasure(?string $treasure): self
    {
        $this->treasure = $treasure;

        return $this;
    }

    public function getPinOverride(): ?string
    {
        return $this->pinOverride;
    }

    public function setPinOverride(?string $pinOverride): self
    {
        $this->pinOverride = $pinOverride;

        return $this;
    }

    public function getAura(): ?Aura
    {
        return $this->aura;
    }

    public function setAura(?Aura $aura): self
    {
        $this->aura = $aura;

        return $this;
    }
}
