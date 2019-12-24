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
 */
class PetGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"petGroup"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Pet", inversedBy="groups")
     */
    private $members;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"petGroup"})
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
     */
    private $createdOn;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->createdOn = new \DateTimeImmutable();
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

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    public function getSkillRollTotal(): int
    {
        return $this->skillRollTotal;
    }

    public function setSkillRollTotal(int $skillRollTotal): self
    {
        $this->skillRollTotal = $skillRollTotal;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }
}
