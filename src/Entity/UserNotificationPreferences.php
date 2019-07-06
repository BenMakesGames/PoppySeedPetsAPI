<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserNotificationPreferencesRepository")
 */
class UserNotificationPreferences
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="userNotificationPreferences", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"notificationPreferences"})
     */
    private $emailNewNews = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"notificationPreferences"})
     */
    private $pushNewNews = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"notificationPreferences"})
     */
    private $emailPetReminders = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"notificationPreferences"})
     */
    private $pushPetReminders = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEmailNewNews(): ?bool
    {
        return $this->emailNewNews;
    }

    public function setEmailNewNews(bool $emailNewNews): self
    {
        $this->emailNewNews = $emailNewNews;

        return $this;
    }

    public function getPushNewNews(): ?bool
    {
        return $this->pushNewNews;
    }

    public function setPushNewNews(bool $pushNewNews): self
    {
        $this->pushNewNews = $pushNewNews;

        return $this;
    }

    public function getEmailPetReminders(): ?int
    {
        return $this->emailPetReminders;
    }

    public function setEmailPetReminders(?int $emailPetReminders): self
    {
        $this->emailPetReminders = $emailPetReminders;

        return $this;
    }

    public function getPushPetReminders(): ?int
    {
        return $this->pushPetReminders;
    }

    public function setPushPetReminders(?int $pushPetReminders): self
    {
        $this->pushPetReminders = $pushPetReminders;

        return $this;
    }
}
