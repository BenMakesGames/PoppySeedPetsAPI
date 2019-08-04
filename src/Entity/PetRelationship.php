<?php

namespace App\Entity;

use App\Functions\NumberFunctions;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetRelationshipRepository")
 */
class PetRelationship
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet", inversedBy="petRelationships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pet")
     * @ORM\JoinColumn(nullable=false)
     */
    private $relationship;

    /**
     * @ORM\Column(type="integer")
     */
    private $intimacy;

    /**
     * @ORM\Column(type="integer")
     */
    private $passion;

    /**
     * @ORM\Column(type="integer")
     */
    private $commitment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $metDescription;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $metOn;

    public function __construct()
    {
        $this->metOn = new \DateTimeImmutable();
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

    public function getRelationship(): ?Pet
    {
        return $this->relationship;
    }

    public function setRelationship(?Pet $relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getIntimacy(): int
    {
        return $this->intimacy;
    }

    public function increaseIntimacy(int $intimacy): self
    {
        $this->intimacy = NumberFunctions::constrain($this->intimacy + $intimacy, 0, 1000);

        return $this;
    }

    public function getPassion(): int
    {
        return $this->passion;
    }

    public function increasePassion(int $passion): self
    {
        $this->passion = NumberFunctions::constrain($this->passion + $passion, 0, 1000);

        return $this;
    }

    public function getCommitment(): int
    {
        return $this->commitment;
    }

    public function increaseCommitment(int $commitment): self
    {
        $this->commitment = NumberFunctions::constrain($this->commitment + $commitment, 0, 1000);

        return $this;
    }

    public function getMetDescription(): ?string
    {
        return $this->metDescription;
    }

    public function setMetDescription(string $metDescription): self
    {
        $this->metDescription = $metDescription;

        return $this;
    }

    public function getMetOn(): \DateTimeImmutable
    {
        return $this->metOn;
    }
}
