<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SpiritCompanionRepository")
 */
class SpiritCompanion
{
    const NAMES = [
        'Duša',
        'Achak',
        'Avira',
        'Cheveyo',
        'Drogo',
        'Du\'an',
        'Espiridión',
        'Euthymios',
        'Fravardin',
        'Gogo',
        'Hew',
        'Imamu',
        'Kneph',
        'Menos',
        'Spyridon',
        'Arima',
        'Efrit',
        'Enid',
        'Janan',
        'Pneuma',
        'Psykhe',
        'Rei',
        'Tien',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $skill;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", mappedBy="spiritCompanion", cascade={"persist", "remove"})
     */
    private $pet;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getSkill(): ?string
    {
        return $this->skill;
    }

    public function setSkill(string $skill): self
    {
        $this->skill = $skill;

        return $this;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): self
    {
        $this->pet = $pet;

        // set (or unset) the owning side of the relation if necessary
        $newSpiritCompanion = $pet === null ? null : $this;
        if ($newSpiritCompanion !== $pet->getSpiritCompanion()) {
            $pet->setSpiritCompanion($newSpiritCompanion);
        }

        return $this;
    }
}
