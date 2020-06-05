<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\PetGroupTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetGroupRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="created_on_idx", columns={"created_on"}),
 *     @ORM\Index(name="last_met_on_idx", columns={"last_met_on"}),
 *     @ORM\Index(name="type_idx", columns={"type"}),
 *     @ORM\Index(name="name_idx", columns={"name"}),
 *     @ORM\Index(name="social_energy_idx", columns={"social_energy"}),
 * })
 */
class PetGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"petGroup", "petGroupDetails", "petGroupIndex", "petPublicProfile"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Pet", inversedBy="groups")
     * @Groups({"petGroupDetails"})
     */
    private $members;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"petGroup", "petGroupDetails", "petGroupIndex", "petPublicProfile"})
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"petGroup"})
     */
    private $progress = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $skillRollTotal = 0;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petGroupDetails", "petGroupIndex", "petPublicProfile"})
     */
    private $createdOn;

    /**
     * @ORM\Column(type="string", length=60)
     * @Groups({"petGroup", "petGroupDetails", "petGroupIndex", "petPublicProfile"})
     */
    private $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"petGroupDetails", "petGroupIndex"})
     */
    private $lastMetOn;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"petGroupDetails"})
     */
    private $numberOfProducts = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $socialEnergy = 0;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->createdOn = new \DateTimeImmutable();
        $this->lastMetOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Pet[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Pet $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(Pet $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
        }

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function setType(int $type): self
    {
        if(!PetGroupTypeEnum::isAValue($type))
            throw new EnumInvalidValueException(PetGroupTypeEnum::class, $type);

        $this->type = $type;

        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function increaseProgress(int $progress): self
    {
        $this->progress += $progress;

        return $this;
    }

    public function clearProgress(): self
    {
        $this->progress = 0;
        $this->skillRollTotal = 0;

        return $this;
    }

    public function getSkillRollTotal(): int
    {
        return $this->skillRollTotal;
    }

    public function increaseSkillRollTotal(int $skillRoll): self
    {
        $this->skillRollTotal += $skillRoll;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastMetOn(): \DateTimeImmutable
    {
        return $this->lastMetOn;
    }

    public function setLastMetOn(): self
    {
        $this->lastMetOn = new \DateTimeImmutable();

        return $this;
    }

    public function getMinimumSize(): int
    {
        switch($this->type)
        {
            case PetGroupTypeEnum::BAND: return 2;
            case PetGroupTypeEnum::ASTRONOMY: return 2;
            default: throw new \Exception('Unhandled group type in group::getMinimumSize');
        }
    }

    public function getMaximumSize(): int
    {
        switch($this->type)
        {
            case PetGroupTypeEnum::BAND: return 5;
            case PetGroupTypeEnum::ASTRONOMY: return 6;
            default: throw new \Exception('Unhandled group type in group::getMaximumSize');
        }
    }

    /**
     * @Groups({"petPublicProfile"})
     */
    public function getMemberCount(): int
    {
        return $this->members->count();
    }

    public function getNumberOfProducts(): int
    {
        return $this->numberOfProducts;
    }

    public function increaseNumberOfProducts(): self
    {
        $this->numberOfProducts += 1;

        return $this;
    }

    public function getSocialEnergy(): int
    {
        return $this->socialEnergy;
    }

    public function spendSocialEnergy(int $socialEnergy): self
    {
        $this->socialEnergy -= $socialEnergy;

        return $this;
    }
}
