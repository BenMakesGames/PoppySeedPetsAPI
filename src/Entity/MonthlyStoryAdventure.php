<?php

namespace App\Entity;

use App\Repository\MonthlyStoryAdventureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MonthlyStoryAdventureRepository::class)
 */
class MonthlyStoryAdventure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({ "starKindredStory" })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({ "starKindredStory" })
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({ "starKindredStory" })
     */
    private $summary;

    /**
     * @ORM\Column(type="integer")
     * @Groups({ "starKindredStory" })
     */
    private $releaseNumber;

    /**
     * @ORM\Column(type="integer")
     * @Groups({ "starKindredStory", "starKindredStoryDetails" })
     */
    private $releaseYear;

    /**
     * @ORM\Column(type="integer")
     * @Groups({ "starKindredStory", "starKindredStoryDetails" })
     */
    private $releaseMonth;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({ "starKindredStoryDetails" })
     */
    private $isDark;

    /**
     * @ORM\OneToMany(targetEntity=MonthlyStoryAdventureStep::class, mappedBy="adventure", orphanRemoval=true)
     */
    private $steps;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getReleaseNumber(): ?int
    {
        return $this->releaseNumber;
    }

    public function setReleaseNumber(int $releaseNumber): self
    {
        $this->releaseNumber = $releaseNumber;

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(int $releaseYear): self
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    public function getReleaseMonth(): ?int
    {
        return $this->releaseMonth;
    }

    public function setReleaseMonth(int $releaseMonth): self
    {
        $this->releaseMonth = $releaseMonth;

        return $this;
    }

    public function getIsDark(): ?bool
    {
        return $this->isDark;
    }

    public function setIsDark(bool $isDark): self
    {
        $this->isDark = $isDark;

        return $this;
    }

    /**
     * @return Collection<int, MonthlyStoryAdventureStep>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(MonthlyStoryAdventureStep $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps[] = $step;
            $step->setAdventure($this);
        }

        return $this;
    }

    public function removeStep(MonthlyStoryAdventureStep $step): self
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getAdventure() === $this) {
                $step->setAdventure(null);
            }
        }

        return $this;
    }
}
