<?php

namespace App\Entity;

use App\Enum\SpiritCompanionStarEnum;
use App\Functions\ArrayFunctions;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SpiritCompanionRepository")
 */
class SpiritCompanion
{
    const NAMES = [
        'Achak', 'Anda', 'Arima', 'Avira',
        'Cheveyo',
        'Drogo', 'Du\'an', 'Duša', 'Dvasia',
        'Efrit', 'Enid', 'Espiridión', 'Euthymios',
        'Fravardin',
        'Gogo', 'Gees',
        'Hew',
        'Imamu',
        'Janan', 'Jīngshén',
        'Kneph',
        'Mamua', 'Matoatoa', 'Menos', 'Muuqasho',
        'Pneuma', 'Psykhe', 'Púca', 'Pӯҳ',
        'Rei', 'Roho',
        'Spøgelse', 'Spyridon',
        'Tien', 'Tinh Thần',
        'Umoya',
        'Wairua',
        'Ysbryd',
    ];

    const IMAGES = [ 'blob', 'dino', 'erm', 'splat', 'jellyfish', 'sooty', 'cat-snake', 'haha', 'icicle', 'square' ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"spiritCompanionPublicProfile"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "spiritCompanionPublicProfile"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "parkEvent", "hollowEarth", "petPublicProfile", "petGroupDetails", "spiritCompanionPublicProfile"})
     */
    private $image;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pet", mappedBy="spiritCompanion")
     * @Groups({"spiritCompanionPublicProfile"})
     */
    private $pet;

    /**
     * @ORM\Column(type="string", length=40)
     * @Groups({"myPet", "spiritCompanionPublicProfile"})
     */
    private $star;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $lastHangOut;

    public function __construct()
    {
        $this->star = SpiritCompanionStarEnum::getRandomValue();
        $this->name = ArrayFunctions::pick_one(self::NAMES);
        $this->image = ArrayFunctions::pick_one(self::IMAGES);
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

    public function getLastHangOut(): ?\DateTimeImmutable
    {
        return $this->lastHangOut;
    }

    public function setLastHangOut(): self
    {
        $this->lastHangOut = new \DateTimeImmutable();

        return $this;
    }
}
