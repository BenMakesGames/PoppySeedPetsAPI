<?php
namespace App\Model;

use App\Entity\PetSpecies;
use Symfony\Component\Serializer\Annotation\Groups;

class PetShelterPet
{
    /**
     * @var int
     * @Groups({"petShelterPet"})
     */
    public $id;

    /**
     * @var string
     * @Groups({"petShelterPet"})
     */
    public $name;

    /**
     * @var PetSpecies
     * @Groups({"petShelterPet"})
     */
    public $species;

    /**
     * @var string
     * @Groups({"petShelterPet"})
     */
    public $colorA;

    /**
     * @var string
     * @Groups({"petShelterPet"})
     */
    public $colorB;
}