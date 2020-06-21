<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetRelationshipRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="commitment_idx", columns={"commitment"}),
 * })
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
     * @var Pet
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

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petFriend"})
     */
    private $lastMet;

    /**
     * @ORM\Column(type="integer")
     */
    private $commitment;

    public function __construct()
    {
        $this->metOn = new \DateTimeImmutable();
        $this->lastMet = new \DateTimeImmutable();
        $this->timeUntilChange = mt_rand(mt_rand(20, 30), mt_rand(50, 80));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"petFriend"})
     */
    public function getRelationshipWanted(): ?string
    {
        if($this->pet->hasMerit(MeritEnum::INTROSPECTIVE))
            return $this->relationshipGoal;
        else
            return null;
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

    /**
     * @throws EnumInvalidValueException
     */
    public function setCurrentRelationship(string $currentRelationship): self
    {
        if(!RelationshipEnum::isAValue($currentRelationship))
            throw new EnumInvalidValueException(RelationshipEnum::class, $currentRelationship);

        $this->currentRelationship = $currentRelationship;

        return $this;
    }

    public function getRelationshipGoal(): string
    {
        return $this->relationshipGoal;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function setRelationshipGoal(string $relationshipGoal): self
    {
        if(!RelationshipEnum::isAValue($relationshipGoal))
            throw new EnumInvalidValueException(RelationshipEnum::class, $relationshipGoal);

        if($this->getPet()->hasMerit(MeritEnum::NAIVE))
        {
            if($relationshipGoal === RelationshipEnum::FRIENDLY_RIVAL)
                $relationshipGoal = RelationshipEnum::FRIEND;
            else if($relationshipGoal === RelationshipEnum::FWB)
                $relationshipGoal = RelationshipEnum::MATE;
        }

        $this->relationshipGoal = $relationshipGoal;

        return $this;
    }

    public function getTimeUntilChange(): ?int
    {
        return $this->timeUntilChange;
    }

    public function setTimeUntilChange()
    {
        $this->timeUntilChange = mt_rand(mt_rand(20, 30), mt_rand(40, 50));
    }

    public function decrementTimeUntilChange(float $multiplier = 1): self
    {
        if($this->wantsDifferentRelationship())
        {
            if($this->pet->hasMerit(MeritEnum::INTROSPECTIVE))
                $this->timeUntilChange -= ceil(3 * $multiplier);
            else
                $this->timeUntilChange -= ceil(2 * $multiplier);
        }

        return $this;
    }

    public function wantsDifferentRelationship(): bool
    {
        return $this->currentRelationship !== $this->relationshipGoal;
    }

    public function getHappiness(): int
    {
        switch($this->currentRelationship)
        {
            case RelationshipEnum::BROKE_UP: return -80; break;
            case RelationshipEnum::DISLIKE: return -35; break;
            case RelationshipEnum::FRIENDLY_RIVAL: return 0; break;
            case RelationshipEnum::FRIEND: return 35; break;
            case RelationshipEnum::BFF: return 65; break;
            case RelationshipEnum::FWB: return 70; break;
            case RelationshipEnum::MATE: return 100; break;
            default: return 0;
        }
    }

    public function getLastMet(): \DateTimeImmutable
    {
        return $this->lastMet;
    }

    public function setLastMet(): self
    {
        $this->lastMet = new \DateTimeImmutable();

        return $this;
    }

    public function getCommitment(): ?int
    {
        return $this->commitment;
    }

    public function setCommitment(int $commitment): self
    {
        $this->commitment = max(0, $commitment);

        return $this;
    }

    public function increaseCommitment(int $amount): self
    {
        $this->commitment = max(0, $this->commitment + $amount);

        return $this;
    }
}
