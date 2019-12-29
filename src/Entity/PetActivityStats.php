<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetActivityStatsRepository")
 */
class PetActivityStats
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", inversedBy="petActivityStats", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\Column(type="integer")
     */
    private $craftSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $craftFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $craftTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $magicBindSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $magicBindFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $magicBindTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $smithSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $smithFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $smithTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $plasticPrintSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $plasticPrintFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $plasticPrintTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fishSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fishFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $fishTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $gatherSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $gatherFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $gatherTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $huntSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $huntFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $huntTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $protocol7Success = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $protocol7Failure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $protocol7Time = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $programSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $programFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $programTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $umbraSuccess = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $umbraFailure = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $umbraTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $hangOut = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $hangOutTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $parkEvent = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $parkEventTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $other = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $otherTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $groupBandTime = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $groupBandSuccess = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getCraftSuccess(): int
    {
        return $this->craftSuccess;
    }

    public function increaseCraftSuccess(int $amount = 1): self
    {
        $this->craftSuccess += $amount;

        return $this;
    }

    public function getCraftFailure(): int
    {
        return $this->craftFailure;
    }

    public function increaseCraftFailure(int $amount = 1): self
    {
        $this->craftFailure += $amount;

        return $this;
    }

    public function getMagicBindSuccess(): int
    {
        return $this->magicBindSuccess;
    }

    public function increaseMagicBindSuccess(int $amount = 1): self
    {
        $this->magicBindSuccess += $amount;

        return $this;
    }

    public function getMagicBindFailure(): int
    {
        return $this->magicBindFailure;
    }

    public function increaseMagicBindFailure(int $amount = 1): self
    {
        $this->magicBindFailure += $amount;

        return $this;
    }

    public function getSmithSuccess(): int
    {
        return $this->smithSuccess;
    }

    public function increaseSmithSuccess(int $amount = 1): self
    {
        $this->smithSuccess += $amount;

        return $this;
    }

    public function getSmithFailure(): int
    {
        return $this->smithFailure;
    }

    public function increaseSmithFailure(int $amount = 1): self
    {
        $this->smithFailure += $amount;

        return $this;
    }

    public function getPlasticPrintSuccess(): int
    {
        return $this->plasticPrintSuccess;
    }

    public function increasePlasticPrintSuccess(int $amount = 1): self
    {
        $this->plasticPrintSuccess += $amount;

        return $this;
    }

    public function getPlasticPrintFailure(): int
    {
        return $this->plasticPrintFailure;
    }

    public function increasePlasticPrintFailure(int $amount = 1): self
    {
        $this->plasticPrintFailure += $amount;

        return $this;
    }

    public function getFishSuccess(): int
    {
        return $this->fishSuccess;
    }

    public function increaseFishSuccess(int $amount = 1): self
    {
        $this->fishSuccess += $amount;

        return $this;
    }

    public function getFishFailure(): int
    {
        return $this->fishFailure;
    }

    public function increaseFishFailure(int $amount = 1): self
    {
        $this->fishFailure += $amount;

        return $this;
    }

    public function getGatherSuccess(): int
    {
        return $this->gatherSuccess;
    }

    public function increaseGatherSuccess(int $amount = 1): self
    {
        $this->gatherSuccess += $amount;

        return $this;
    }

    public function getGatherFailure(): int
    {
        return $this->gatherFailure;
    }

    public function increaseGatherFailure(int $amount = 1): self
    {
        $this->gatherFailure += $amount;

        return $this;
    }

    public function getHuntSuccess(): int
    {
        return $this->huntSuccess;
    }

    public function increaseHuntSuccess(int $amount = 1): self
    {
        $this->huntSuccess += $amount;

        return $this;
    }

    public function getHuntFailure(): int
    {
        return $this->huntFailure;
    }

    public function increaseHuntFailure(int $amount = 1): self
    {
        $this->huntFailure += $amount;

        return $this;
    }

    public function getProtocol7Success(): int
    {
        return $this->protocol7Success;
    }

    public function increaseProtocol7Success(int $amount = 1): self
    {
        $this->protocol7Success += $amount;

        return $this;
    }

    public function getProtocol7Failure(): int
    {
        return $this->protocol7Failure;
    }

    public function increaseProtocol7Failure(int $amount = 1): self
    {
        $this->protocol7Failure += $amount;

        return $this;
    }

    public function getProgramSuccess(): int
    {
        return $this->programSuccess;
    }

    public function increaseProgramSuccess(int $amount = 1): self
    {
        $this->programSuccess += $amount;

        return $this;
    }

    public function getProgramFailure(): int
    {
        return $this->programFailure;
    }

    public function increaseProgramFailure(int $amount = 1): self
    {
        $this->programFailure += $amount;

        return $this;
    }

    public function getUmbraSuccess(): int
    {
        return $this->umbraSuccess;
    }

    public function increaseUmbraSuccess(int $amount = 1): self
    {
        $this->umbraSuccess += $amount;

        return $this;
    }

    public function getUmbraFailure(): int
    {
        return $this->umbraFailure;
    }

    public function increaseUmbraFailure(int $amount = 1): self
    {
        $this->umbraFailure += $amount;

        return $this;
    }

    public function getHangOut(): int
    {
        return $this->hangOut;
    }

    public function increaseHangOut(int $amount = 1): self
    {
        $this->hangOut += $amount;

        return $this;
    }

    public function getParkEvent(): ?int
    {
        return $this->parkEvent;
    }

    public function increaseParkEvent(int $amount = 1): self
    {
        $this->parkEvent += $amount;

        return $this;
    }

    public function getOther(): int
    {
        return $this->other;
    }

    public function increaseOther(int $amount = 1): self
    {
        $this->other += $amount;

        return $this;
    }

    public function getCraftTime(): int
    {
        return $this->craftTime;
    }

    public function increaseCraftTime(int $amount): self
    {
        $this->craftTime += $amount;

        return $this;
    }

    public function getMagicBindTime(): int
    {
        return $this->magicBindTime;
    }

    public function increaseMagicBindTime(int $amount): self
    {
        $this->magicBindTime += $amount;

        return $this;
    }

    public function getSmithTime(): int
    {
        return $this->smithTime;
    }

    public function increaseSmithTime(int $amount): self
    {
        $this->smithTime += $amount;

        return $this;
    }

    public function getPlasticPrintTime(): int
    {
        return $this->plasticPrintTime;
    }

    public function increasePlasticPrintTime(int $amount): self
    {
        $this->plasticPrintTime += $amount;

        return $this;
    }

    public function getFishTime(): int
    {
        return $this->fishTime;
    }

    public function increaseFishTime(int $amount): self
    {
        $this->fishTime += $amount;

        return $this;
    }

    public function getGatherTime(): int
    {
        return $this->gatherTime;
    }

    public function increaseGatherTime(int $amount): self
    {
        $this->gatherTime += $amount;

        return $this;
    }

    public function getHuntTime(): int
    {
        return $this->huntTime;
    }

    public function increaseHuntTime(int $amount): self
    {
        $this->huntTime += $amount;

        return $this;
    }

    public function getProtocol7Time(): int
    {
        return $this->protocol7Time;
    }

    public function increaseProtocol7Time(int $amount): self
    {
        $this->protocol7Time += $amount;

        return $this;
    }

    public function getProgramTime(): int
    {
        return $this->programTime;
    }

    public function increaseProgramTime(int $amount): self
    {
        $this->programTime += $amount;

        return $this;
    }

    public function getUmbraTime(): int
    {
        return $this->umbraTime;
    }

    public function increaseUmbraTime(int $amount): self
    {
        $this->umbraTime += $amount;

        return $this;
    }

    public function getParkEventTime(): int
    {
        return $this->parkEventTime;
    }

    public function increaseParkEventTime(int $amount): self
    {
        $this->parkEventTime += $amount;

        return $this;
    }

    public function getHangOutTime(): int
    {
        return $this->hangOutTime;
    }

    public function increaseHangOutTime(int $amount): self
    {
        $this->hangOutTime += $amount;

        return $this;
    }

    public function getOtherTime(): int
    {
        return $this->otherTime;
    }

    public function increaseOtherTime(int $amount): self
    {
        $this->otherTime += $amount;

        return $this;
    }

    public function getGroupBandTime(): ?int
    {
        return $this->groupBandTime;
    }

    public function setGroupBandTime(int $groupBandTime): self
    {
        $this->groupBandTime = $groupBandTime;

        return $this;
    }

    public function getGroupBandSuccess(): ?int
    {
        return $this->groupBandSuccess;
    }

    public function setGroupBandSuccess(int $groupBandSuccess): self
    {
        $this->groupBandSuccess = $groupBandSuccess;

        return $this;
    }
}
