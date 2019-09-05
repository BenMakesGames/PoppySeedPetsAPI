<?php

namespace App\Entity;

use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups({"petFriend"})
     */
    private $relationship;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"petFriend"})
     */
    private $metDescription;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petFriend"})
     */
    private $metOn;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"petFriend"})
     */
    private $currentRelationship;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $relationshipGoal;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeUntilChange;

    public function __construct()
    {
        $this->metOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    public function setPet(Pet $pet): self
    {
        $this->pet = $pet;

        return $this;
    }

    public function getRelationship(): Pet
    {
        return $this->relationship;
    }

    public function setRelationship(Pet $relationship): self
    {
        $this->relationship = $relationship;

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

    public function getCurrentRelationship(): string
    {
        return $this->currentRelationship;
    }

    public function setCurrentRelationship(string $currentRelationship): self
    {
        if(!RelationshipEnum::isAValue($currentRelationship)) throw new \InvalidArgumentException('currentRelationship is not a valid RelationshipEnum value.');

        $this->currentRelationship = $currentRelationship;

        return $this;
    }

    public function getRelationshipGoal(): string
    {
        return $this->relationshipGoal;
    }

    public function setRelationshipGoal(string $relationshipGoal): self
    {
        if(!RelationshipEnum::isAValue($relationshipGoal)) throw new \InvalidArgumentException('relationshipGoal is not a valid RelationshipEnum value.');

        $this->relationshipGoal = $relationshipGoal;

        return $this;
    }

    public function getTimeUntilChange(): ?int
    {
        return $this->timeUntilChange;
    }

    public function setTimeUntilChange(int $timeUntilChange): self
    {
        $this->timeUntilChange = $timeUntilChange;

        return $this;
    }

    public function getCommitment(): int
    {
        $commitment = 0;

        switch($this->currentRelationship)
        {
            case RelationshipEnum::BROKE_UP: $commitment = 0; break;
            case RelationshipEnum::DISLIKE: $commitment = 0; break;
            case RelationshipEnum::FRIEND: $commitment = 2; break;
            case RelationshipEnum::FRIENDLY_RIVAL: $commitment = 3; break;
            case RelationshipEnum::BFF: $commitment = 5; break;
            case RelationshipEnum::FWB: $commitment = 5; break;
            case RelationshipEnum::MATE: $commitment = 8; break;
        }

        if($this->currentRelationship === $this->relationshipGoal)
            $commitment += round($commitment / 4);

        return $commitment;
    }
}
