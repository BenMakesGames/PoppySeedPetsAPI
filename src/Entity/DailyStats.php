<?php

namespace App\Entity;

use App\Repository\DailyStatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DailyStatsRepository::class)
 */
class DailyStats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOfPlayers1Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOfPlayers3Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOfPlayers7Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOfPlayers28Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOfPlayersLifetime;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMoneys1Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMoneys3Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMoneys7Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMoneys28Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMoneysLifetime;

    /**
     * @ORM\Column(type="integer")
     */
    private $newPlayers1Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $newPlayers3Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $newPlayers7Day;

    /**
     * @ORM\Column(type="integer")
     */
    private $newPlayers28Day;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getNumberOfPlayers1Day(): ?int
    {
        return $this->numberOfPlayers1Day;
    }

    public function setNumberOfPlayers1Day(int $numberOfPlayers1Day): self
    {
        $this->numberOfPlayers1Day = $numberOfPlayers1Day;

        return $this;
    }

    public function getNumberOfPlayers3Day(): ?int
    {
        return $this->numberOfPlayers3Day;
    }

    public function setNumberOfPlayers3Day(int $numberOfPlayers3Day): self
    {
        $this->numberOfPlayers3Day = $numberOfPlayers3Day;

        return $this;
    }

    public function getNumberOfPlayers7Day(): ?int
    {
        return $this->numberOfPlayers7Day;
    }

    public function setNumberOfPlayers7Day(int $numberOfPlayers7Day): self
    {
        $this->numberOfPlayers7Day = $numberOfPlayers7Day;

        return $this;
    }

    public function getNumberOfPlayers28Day(): ?int
    {
        return $this->numberOfPlayers28Day;
    }

    public function setNumberOfPlayers28Day(int $numberOfPlayers28Day): self
    {
        $this->numberOfPlayers28Day = $numberOfPlayers28Day;

        return $this;
    }

    public function getNumberOfPlayersLifetime(): ?int
    {
        return $this->numberOfPlayersLifetime;
    }

    public function setNumberOfPlayersLifetime(int $numberOfPlayersLifetime): self
    {
        $this->numberOfPlayersLifetime = $numberOfPlayersLifetime;

        return $this;
    }

    public function getTotalMoneys1Day(): ?int
    {
        return $this->totalMoneys1Day;
    }

    public function setTotalMoneys1Day(int $totalMoneys1Day): self
    {
        $this->totalMoneys1Day = $totalMoneys1Day;

        return $this;
    }

    public function getTotalMoneys3Day(): ?int
    {
        return $this->totalMoneys3Day;
    }

    public function setTotalMoneys3Day(int $totalMoneys3Day): self
    {
        $this->totalMoneys3Day = $totalMoneys3Day;

        return $this;
    }

    public function getTotalMoneys7Day(): ?int
    {
        return $this->totalMoneys7Day;
    }

    public function setTotalMoneys7Day(int $totalMoneys7Day): self
    {
        $this->totalMoneys7Day = $totalMoneys7Day;

        return $this;
    }

    public function getTotalMoneys28Day(): ?int
    {
        return $this->totalMoneys28Day;
    }

    public function setTotalMoneys28Day(int $totalMoneys28Day): self
    {
        $this->totalMoneys28Day = $totalMoneys28Day;

        return $this;
    }

    public function getTotalMoneysLifetime(): ?int
    {
        return $this->totalMoneysLifetime;
    }

    public function setTotalMoneysLifetime(int $totalMoneysLifetime): self
    {
        $this->totalMoneysLifetime = $totalMoneysLifetime;

        return $this;
    }

    public function getNewPlayers1Day(): ?int
    {
        return $this->newPlayers1Day;
    }

    public function setNewPlayers1Day(int $newPlayers1Day): self
    {
        $this->newPlayers1Day = $newPlayers1Day;

        return $this;
    }

    public function getNewPlayers3Day(): ?int
    {
        return $this->newPlayers3Day;
    }

    public function setNewPlayers3Day(int $newPlayers3Day): self
    {
        $this->newPlayers3Day = $newPlayers3Day;

        return $this;
    }

    public function getNewPlayers7Day(): ?int
    {
        return $this->newPlayers7Day;
    }

    public function setNewPlayers7Day(int $newPlayers7Day): self
    {
        $this->newPlayers7Day = $newPlayers7Day;

        return $this;
    }

    public function getNewPlayers28Day(): ?int
    {
        return $this->newPlayers28Day;
    }

    public function setNewPlayers28Day(int $newPlayers28Day): self
    {
        $this->newPlayers28Day = $newPlayers28Day;

        return $this;
    }
}
