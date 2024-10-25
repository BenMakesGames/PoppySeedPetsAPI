<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\RelationshipEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Table]
#[ORM\Index(name: 'commitment_idx', columns: ['commitment'])]
#[ORM\Index(name: 'current_relationship_idx', columns: ['current_relationship'])]
#[ORM\Entity(repositoryClass: 'App\Repository\PetRelationshipRepository')]
class PetRelationship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var Pet
     */
    #[ORM\ManyToOne(targetEntity: Pet::class, inversedBy: 'petRelationships')]
    #[ORM\JoinColumn(nullable: false)]
    private $pet;

    #[Groups(["petFriend"])]
    #[ORM\ManyToOne(targetEntity: Pet::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $relationship;

    #[ORM\Column(type: 'string', length: 255)]
    private $metDescription;

    #[Groups(["petFriend"])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $metOn;

    #[Groups(["petFriend"])]
    #[ORM\Column(type: 'string', length: 40)]
    private $currentRelationship;

    #[ORM\Column(type: 'string', length: 40)]
    private $relationshipGoal;

    #[ORM\Column(type: 'integer')]
    private $timeUntilChange;

    #[Groups(["petFriend"])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $lastMet;

    #[ORM\Column(type: 'integer')]
    private $commitment;

    #[Groups(["petFriend"])]
    #[SerializedName("commitment")]
    #[ORM\Column(type: 'smallint')]
    private $rating = 0;

    public function __construct()
    {
        $this->metOn = new \DateTimeImmutable();
        $this->lastMet = new \DateTimeImmutable();
        $this->timeUntilChange = random_int(random_int(20, 30), random_int(50, 80));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(["petFriend"])]
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

        $this->relationshipGoal = $relationshipGoal;

        return $this;
    }

    public function getTimeUntilChange(): ?int
    {
        return $this->timeUntilChange;
    }

    public function setTimeUntilChange()
    {
        $this->timeUntilChange = random_int(random_int(20, 30), random_int(40, 50));
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
        return match ($this->currentRelationship)
        {
            RelationshipEnum::BROKE_UP => -80,
            RelationshipEnum::DISLIKE => -35,
            RelationshipEnum::FRIENDLY_RIVAL => 0,
            RelationshipEnum::FRIEND => 35,
            RelationshipEnum::BFF => 65,
            RelationshipEnum::FWB => 70,
            RelationshipEnum::MATE => 100,
            default => 0,
        };
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

    /**
     * @Groups({"petFriend"})
     * @SerializedName("metDescription")
     */
    public function getFormattedMetDescription()
    {
        return str_replace(
            [ '%pet.name%', '%relationship.name%' ],
            [ $this->getPet()->getName(), $this->getRelationship()->getName() ],
            $this->getMetDescription()
        );
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }
}
