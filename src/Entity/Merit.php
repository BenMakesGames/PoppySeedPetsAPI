<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Merit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Groups(["availableMerits", "myPet", "meritEncyclopedia", "userPublicProfile", "petPublicProfile", "petGroupDetails", "parkEvent", "petFriend", "hollowEarth", "petActivityLogAndPublicPet", "helperPet"])]
    #[ORM\Column(type: 'string', length: 30, unique: true)]
    private $name;

    #[Groups(["availableMerits", "meritEncyclopedia"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
}
