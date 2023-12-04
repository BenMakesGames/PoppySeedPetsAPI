<?php

namespace App\Entity;

use App\Enum\EnumInvalidValueException;
use App\Enum\FieldGuideEntryTypeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FieldGuideEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20)]
    private $type;

    #[ORM\Column(type: 'string', length: 40, unique: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private $image;

    #[ORM\Column(type: 'text')]
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if(!FieldGuideEntryTypeEnum::isAValue($type))
            throw new EnumInvalidValueException(FieldGuideEntryTypeEnum::class, $type);

        $this->type = $type;

        return $this;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
