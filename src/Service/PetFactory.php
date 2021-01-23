<?php
namespace App\Service;

use App\Entity\Merit;
use App\Entity\Pet;
use App\Entity\PetHouseTime;
use App\Entity\PetSkills;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Model\PetShelterPet;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetFactory
{
    private $em;
    private $petRepository;
    private $meritRepository;
    private $squirrel3;

    public function __construct(
        EntityManagerInterface $em, PetRepository $petRepository, MeritRepository $meritRepository, Squirrel3 $squirrel3
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->meritRepository = $meritRepository;
        $this->squirrel3 = $squirrel3;
    }

    public function createPet(User $owner, string $name, PetSpecies $species, string $colorA, string $colorB, string $favoriteFlavor, Merit $startingMerit): Pet
    {
        $petSkills = new PetSkills();

        $this->em->persist($petSkills);

        $pet = (new Pet())
            ->setOwner($owner)
            ->setName($name)
            ->setSpecies($species)
            ->setColorA($colorA)
            ->setColorB($colorB)
            ->setSkills($petSkills)
            ->setFavoriteFlavor($favoriteFlavor)
            ->addMerit($startingMerit)
        ;

        $petHouseTime = (new PetHouseTime())
            ->setSocialEnergy(ceil(PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * (4 + $pet->getExtroverted()) / 4))
        ;

        $pet->setHouseTime($petHouseTime);

        $this->em->persist($petHouseTime);
        $this->em->persist($pet);

        return $pet;
    }

    public function createRandomPetOfSpecies(User $owner, PetSpecies $petSpecies): Pet
    {
        $now = new \DateTimeImmutable();

        $petCount = $this->petRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $now)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $basePet = $this->petRepository->createQueryBuilder('p')
            ->andWhere('p.birthDate<:today')
            ->setParameter('today', $now)
            ->setMaxResults(1)
            ->setFirstResult($this->squirrel3->rngNextInt(0, $petCount - 1))
            ->getQuery()
            ->getSingleResult()
        ;

        $colorA = $this->squirrel3->rngNextTweakedColor($basePet->getColorA());
        $colorB = $this->squirrel3->rngNextTweakedColor($basePet->getColorB());

        $pet = $this->createPet(
            $owner,
            $this->squirrel3->rngNextFromArray(PetShelterPet::PET_NAMES),
            $petSpecies,
            $colorA,
            $colorB,
            FlavorEnum::getRandomValue($this->squirrel3),
            $this->meritRepository->getRandomStartingMerit()
        );

        $pet->setFoodAndSafety($this->squirrel3->rngNextInt(10, 12), -9);

        return $pet;
    }
}
