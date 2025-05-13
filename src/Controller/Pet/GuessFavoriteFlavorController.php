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

use App\Entity\Pet;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetBadgeHelpers;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/pet")]
class GuessFavoriteFlavorController
{
    #[Route("/{pet}/guessFavoriteFlavor", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function guessFavoriteFlavor(
        Pet $pet, Request $request, ResponseService $responseService,
        InventoryService $inventoryService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It\'s not interested in revealing its favorite flavor to you.');

        if($pet->getRevealedFavoriteFlavor())
            throw new PSPInvalidOperationException($pet->getName() . '\'s favorite flavor has already been revealed!');

        $guess = FlavorEnum::tryFrom(strtolower(trim($request->request->getAlpha('flavor'))))
            ?? throw new PSPFormValidationException('Please pick a flavor.');

        $flavorGuesses = UserQuestRepository::findOrCreate($em, $user, 'Flavor Guesses for Pet #' . $pet->getId(), 0);

        if($flavorGuesses->getValue() > 0 && $flavorGuesses->getLastUpdated()->format('Y-m-d') === date('Y-m-d'))
            throw new PSPInvalidOperationException('You already guessed today. Try again tomorrow.');

        $flavorGuesses->setValue($flavorGuesses->getValue() + 1);

        $data = null;

        if($pet->getFavoriteFlavor() === $guess)
        {
            $pet
                ->setRevealedFavoriteFlavor($flavorGuesses->getValue())
                ->increaseAffectionLevel(1)
            ;
            $inventoryService->receiveItem('Heartstone', $user, $user, $user->getName() . ' received this from ' . $pet->getName() . ' for knowing their favorite flavor: ' . $pet->getFavoriteFlavor()->value . '!', LocationEnum::HOME);
            $responseService->setReloadInventory();
            $data = $pet;

            PetBadgeHelpers::awardBadgeAndLog($em, $pet, PetBadgeEnum::REVEALED_FAVORITE_FLAVOR, $user->getName() . ' correctly guessed ' . $pet->getName() . '\'s favorite flavor! A Heartstone materialized in front of their body, and floated into ' . $user->getName() . '\'s hands!');
        }
        else
        {
            $responseService->addFlashMessage('Hm... it seems that wasn\'t correct. ' . $pet->getName() . ' looks a little disappointed. (You can try again, tomorrow.)');
        }

        $em->flush();

        return $responseService->success($data, [ SerializationGroupEnum::MY_PET ]);
    }
}
