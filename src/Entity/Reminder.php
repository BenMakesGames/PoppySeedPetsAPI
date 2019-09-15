<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReminderRepository")
 */
class Reminder
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $text;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $nextReminder;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reminderInterval;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getNextReminder(): ?\DateTimeImmutable
    {
        return $this->nextReminder;
    }

    public function setNextReminder(\DateTimeImmutable $nextReminder): self
    {
        $this->nextReminder = $nextReminder;

        return $this;
    }

    public function updateNextReminder()
    {
        if(!$this->reminderInterval)
            throw new \InvalidArgumentException('Cannot set next reminder; reminder interval is null or 0.');

        $this->setNextReminder($this->getNextReminder()->modify('+' . $this->getReminderInterval() . ' days'));
    }

    public function getReminderInterval(): ?int
    {
        return $this->reminderInterval;
    }

    public function setReminderInterval(?int $reminderInterval): self
    {
        if($reminderInterval < 0)
            throw new \InvalidArgumentException('reminderInterval cannot be less than 0!');
        else if($reminderInterval === 0)
            $reminderInterval = null;

        $this->reminderInterval = $reminderInterval;

        return $this;
    }
}
