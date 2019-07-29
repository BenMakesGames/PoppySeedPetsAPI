<?php

namespace App\Entity;

use App\Model\PetChangesSummary;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetActivityLogRepository")
 */
class PetActivityLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\Column(type="text")
     * @Groups({"petActivityLogs"})
     */
    private $entry;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petActivityLogs"})
     */
    private $createdOn;

    /**
     * @ORM\Column(type="object", nullable=true)
     * @Groups({"petActivityLogs"})
     */
    private $changes;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"petActivityLogs"})
     */
    private $icon;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getEntry(): ?string
    {
        return $this->entry;
    }

    public function setEntry(string $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getChanges(): ?PetChangesSummary
    {
        return $this->changes;
    }

    public function setChanges(?PetChangesSummary $changes): self
    {
        $this->changes = $changes;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
