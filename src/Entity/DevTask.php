<?php

namespace App\Entity;

use App\Enum\DevTaskStatusEnum;
use App\Enum\DevTaskTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DevTaskRepository")
 */
class DevTask
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"devTask"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"devTask"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"devTask"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"devTask"})
     */
    private $createdOn;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"devTask"})
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"devTask"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"devTask"})
     */
    private $releasedOn;

    public function __construct()
    {
        $this->createdOn = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        if(!DevTaskTypeEnum::isAValue($type))
            throw new \InvalidArgumentException('$type is not a valid dev task type.');

        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        if(!DevTaskStatusEnum::isAValue($status))
            throw new \InvalidArgumentException('$status is not a valid dev task status.');

        $this->status = $status;

        if($this->status === DevTaskStatusEnum::RELEASED)
        {
            if($this->releasedOn === null)
                $this->releasedOn = new \DateTimeImmutable();
        }
        else
            $this->releasedOn = null;

        return $this;
    }

    public function getReleasedOn(): ?\DateTimeImmutable
    {
        return $this->releasedOn;
    }
}
