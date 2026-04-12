<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Controller\Pet;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\Pet;
use App\Enum\PetLocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ArrayFunctions;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\UserAccessor;

#[Route("/pet")]
class DaycareController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/daycare/arrange", methods: ["POST"])]
    public function arrangePets(
        ResponseService $responseService,
        #[MapRequestPayload] ArrangePetsRequest $payload,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $petIds = array_unique($payload->pets);

        $user = $userAccessor->getUserOrThrow();

        $petsWantedAtHome = $em->getRepository(Pet::class)->findBy([
            'id' => $petIds,
            'owner' => $user
        ]);

        if(count($petsWantedAtHome) !== count($petIds))
            throw new PSPPetNotFoundException();

        if(count($petsWantedAtHome) > $user->getMaxPets())
            throw new PSPInvalidOperationException('You cannot have more than ' . $user->getMaxPets() . ' pets at home.');

        if(array_any($petsWantedAtHome, fn(Pet $pet) => $pet->getLocation() !== PetLocationEnum::HOME && $pet->getLocation() !== PetLocationEnum::DAYCARE))
            throw new PSPInvalidOperationException('Pets may only be moved between home and/or Daycare.');

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        $petsToMoveToHome = array_filter($petsWantedAtHome, fn(Pet $pet) => $pet->getLocation() === PetLocationEnum::DAYCARE);
        $petsToMoveToDaycare = array_filter($petsAtHome, fn(Pet $pet) => !array_any($petsWantedAtHome, fn(Pet $p) => $p->getId() === $pet->getId()));

        foreach($petsToMoveToHome as $pet)
            self::takePetOutOfDaycare($pet);

        foreach($petsToMoveToDaycare as $pet)
            $pet->setLocation(PetLocationEnum::DAYCARE);

        $em->flush();

        return $responseService->success();
    }

    private static function takePetOutOfDaycare(Pet $pet)
    {
        $hoursInDayCare = (\time() - $pet->getLocationMoveDate()->getTimestamp()) / (60 * 60);

        if($hoursInDayCare >= 4)
        {
            $fourHoursInDayCare = (int)($hoursInDayCare / 4);

            PetExperienceService::spendTimeOnStatusEffects($pet, $fourHoursInDayCare);

            $pet
                ->increasePoison(-$fourHoursInDayCare)
                ->increaseCaffeine(-$fourHoursInDayCare)
                ->increaseAlcohol(-$fourHoursInDayCare)
                ->increasePsychedelic(-$fourHoursInDayCare)
                ->increasePoison(-$fourHoursInDayCare)

                ->increaseFood($fourHoursInDayCare, 12)
                ->increaseSafety($fourHoursInDayCare, 10)
                ->increaseLove($fourHoursInDayCare, 8)
                ->increaseEsteem($fourHoursInDayCare, 6)
            ;
        }

        $pet->setLocation(PetLocationEnum::HOME);
    }
}

class ArrangePetsRequest
{
    public function __construct(
        /** @var int[] */
        #[Assert\All([
            new Assert\Type('int'),
            new Assert\Positive()
        ])]
        public readonly array $pets,
    ) {}
}
