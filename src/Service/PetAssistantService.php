<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\DragonHelpers;
use App\Functions\DragonRepository;
use App\Functions\PetRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetAssistantService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    private static function assertOwnership(User $user, Pet $pet)
    {
        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();
    }

    private static function assertAvailability(Pet $pet)
    {
        if($pet->getLocation() != PetLocationEnum::HOME && $pet->getLocation() != PetLocationEnum::DAYCARE)
            throw new PSPInvalidOperationException('That pet is currently at the ' . $pet->getLocation() . '.');
    }

    private static function assertCanAssignHelpers(User $user)
    {
        if(!$user->getCanAssignHelpers())
            throw new PSPNotUnlockedException('Helpers');
    }

    public static function helpBeehive(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive) || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $beehive = $user->getBeehive();

        if($beehive->getWorkers() < 2000)
            throw new PSPNotUnlockedException('Beehive Helpers');

        if($beehive->getHelper())
            throw new PSPInvalidOperationException('Your beehive already has a helper! ' . $beehive->getHelper()->getName() . '!');

        self::assertAvailability($pet);

        $beehive->setHelper($pet);

        $user->setCanAssignHelpers(true);

        $pet->setLocation(PetLocationEnum::BEEHIVE);
    }

    public static function helpGreenhouse(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse) || !$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        self::assertCanAssignHelpers($user);

        $greenhouse = $user->getGreenhouse();

        if($greenhouse->getHelper())
            throw new PSPInvalidOperationException('Your Greenhouse already has a helper! ' . $greenhouse->getHelper()->getName() . '!');

        self::assertAvailability($pet);

        $greenhouse->setHelper($pet);
        $pet->setLocation(PetLocationEnum::GREENHOUSE);
    }

    public function helpFireplace(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        if(!$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        self::assertCanAssignHelpers($user);

        $fireplace = $user->getFireplace();

        if($fireplace->getHelper())
            throw new PSPInvalidOperationException('Your Fireplace already has a helper! ' . $fireplace->getHelper()->getName() . '!');

        $whelp = DragonRepository::findWhelp($this->em, $user);

        if($whelp)
            throw new PSPInvalidOperationException('There\'s already a dragon living here...');

        self::assertAvailability($pet);

        $fireplace->setHelper($pet);
        $pet->setLocation(PetLocationEnum::FIREPLACE);
    }

    public function helpDragon(User $user, Pet $pet)
    {
        self::assertOwnership($user, $pet);

        $dragon = DragonHelpers::getAdultDragon($this->em, $user);

        if(!$dragon)
            throw new PSPNotUnlockedException('Dragon Den');

        self::assertCanAssignHelpers($user);

        if($dragon->getHelper())
            throw new PSPInvalidOperationException('Your Dragon Den already has a helper! ' . $dragon->getHelper()->getName() . '!');

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
            $dragon = DragonHelpers::getAdultDragon($this->em, $user);

            if($dragon && $user->hasUnlockedFeature(UnlockableFeatureEnum::DragonDen))
                $dragon->setHelper(null);
        }
        else
            throw new PSPInvalidOperationException('That pet is not currently helping out anywhere...');

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($this->em, $user);

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