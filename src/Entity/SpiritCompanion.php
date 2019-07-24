<?php

namespace App\Entity;

use App\Functions\ArrayFunctions;
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

    const STARS = [
        'Altair',
        'Cassiopeia',
        'Cepheus',
        'Gemini',
        'Hydra',
        'Sagittarius'
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
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", mappedBy="spiritCompanion", cascade={"persist", "remove"})
     */
    private $pet;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet"})
     */
    private $star;

    public function __construct()
    {
        $this->star = ArrayFunctions::pick_one(self::STARS);
        $this->name = ArrayFunctions::pick_one(self::NAMES);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function getStar(): string
    {
        return $this->star;
    }
}
