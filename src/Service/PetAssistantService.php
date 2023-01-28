<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Repository\DragonRepository;
use App\Repository\PetRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PetAssistantService
{
    private PetRepository $petRepository;
    private DragonRepository $dragonRepository;

    public function __construct(PetRepository $petRepository, DragonRepository $dragonRepository, Squirrel3 $rng)
    {
        $this->petRepository = $petRepository;
        $this->dragonRepository = $dragonRepository;
    }

    private static function assertOwnership(User $user, Pet $pet)
    {
        if($pet->getOwner() != $user)
            throw new NotFoundHttpException('That pet does not exist, or does not belong to you.');
    }

    private static function assertAvailability(Pet $pet)
    {
        if($pet->getLocation() != PetLocationEnum::HOME && $pet->getLocation() != PetLocationEnum::DAYCARE)
            throw new UnprocessableEntityHttpException('That pet is currently at the ' . $pet->getLocation() . '.');
    }

    private static function assertCanAssignHelpers(User $user)
    {
        if(!$user->getCanAssignHelpers())
            throw new AccessDeniedHttpException('A helper cannot be assigned here... yet.');
    }

    public static function helpBeehive(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $beehive = $user->getBeehive();

        if($beehive->getWorkers() < 2000)
            throw new AccessDeniedHttpException('A helper cannot be assigned to your Beehive... yet.');

        if($beehive->getHelper())
            throw new UnprocessableEntityHttpException('Your beehive already has a helper! ' . $beehive->getHelper()->getName() . '!');

        self::assertAvailability($pet);

        $beehive->setHelper($pet);

        $user->setCanAssignHelpers(true);

        $pet->setLocation(PetLocationEnum::BEEHIVE);
    }

    public static function helpGreenhouse(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        if(!$user->getUnlockedGreenhouse() || !$user->getGreenhouse())
            throw new AccessDeniedHttpException('You haven\'t got a Greenhouse, yet!');

        self::assertCanAssignHelpers($user);

        $greenhouse = $user->getGreenhouse();

        if($greenhouse->getHelper())
            throw new UnprocessableEntityHttpException('Your Greenhouse already has a helper! ' . $greenhouse->getHelper()->getName() . '!');

        self::assertAvailability($pet);

        $greenhouse->setHelper($pet);
        $pet->setLocation(PetLocationEnum::GREENHOUSE);
    }

    public function helpFireplace(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        if(!$user->getFireplace())
            throw new AccessDeniedHttpException('You haven\'t got a Fireplace, yet!');

        self::assertCanAssignHelpers($user);

        $fireplace = $user->getFireplace();

        if($fireplace->getHelper())
            throw new UnprocessableEntityHttpException('Your Fireplace already has a helper! ' . $fireplace->getHelper()->getName() . '!');

        $whelp = $this->dragonRepository->findWhelp($user);

        if($whelp)
            throw new AccessDeniedHttpException('There\'s already a dragon living here...');

        self::assertAvailability($pet);

        $fireplace->setHelper($pet);
        $pet->setLocation(PetLocationEnum::FIREPLACE);
    }

    public function helpDragon(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        $dragon = $this->dragonRepository->findAdult($user);

        if(!$dragon)
            throw new AccessDeniedHttpException('You haven\'t got a Dragon Den, yet!');

        self::assertCanAssignHelpers($user);

        if($dragon->getHelper())
            throw new UnprocessableEntityHttpException('Your Dragon Den already has a helper! ' . $dragon->getHelper()->getName() . '!');

        self::assertAvailability($pet);

        $dragon->setHelper($pet);
        $pet->setLocation(PetLocationEnum::DRAGON_DEN);
    }

    public function stopAssisting(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        if($pet->getLocation() == PetLocationEnum::BEEHIVE)
        {
            if($user->getBeehive())
                $user->getBeehive()->setHelper(null);
        }
        else if($pet->getLocation() == PetLocationEnum::GREENHOUSE)
        {
            if($user->getGreenhouse())
                $user->getGreenhouse()->setHelper(null);
        }
        else if($pet->getLocation() == PetLocationEnum::FIREPLACE)
        {
            if($user->getFireplace())
                $user->getFireplace()->setHelper(null);
        }
        else if($pet->getLocation() == PetLocationEnum::DRAGON_DEN)
        {
            $dragon = $this->dragonRepository->findAdult($user);

            if($dragon && $user->getUnlockedDragonDen())
                $dragon->setHelper(null);
        }
        else
            throw new UnprocessableEntityHttpException('That pet is not currently helping out anywhere...');

        $numberOfPetsAtHome = $this->petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $pet->setLocation(PetLocationEnum::DAYCARE);
        else
            $pet->setLocation(PetLocationEnum::HOME);
    }

    public static function getExtraItem(IRandom $rng, int $totalSkill, array $baseList, array $mediumList, array $highList, array $superHighList)
    {
        $roll = $rng->rngNextInt(1, min(30, 10 + $totalSkill));

        if($roll < 10)
            return $rng->rngNextFromArray($baseList);
        else if($roll < 17)
            return $rng->rngNextFromArray($mediumList);
        else if($roll < 25)
            return $rng->rngNextFromArray($highList);
        else
            return $rng->rngNextFromArray($superHighList);
    }
}