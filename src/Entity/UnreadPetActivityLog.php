<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UnreadPetActivityLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pet::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\OneToOne(targetEntity=PetActivityLog::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $petActivityLog;

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

    public function getPetActivityLog(): ?PetActivityLog
    {
        return $this->petActivityLog;
    }

    public function setPetActivityLog(PetActivityLog $petActivityLog): self
    {
        $this->petActivityLog = $petActivityLog;

        return $this;
    }
}
