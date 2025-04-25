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


namespace App\Controller\MonthlyStoryAdventure;

use App\Entity\MonthlyStoryAdventureStep;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\MonthlyStoryAdventureService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/monthlyStoryAdventure")]
class GoOnAdventure
{
    #[Route("/do/{step}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function handle(
        Request $request,
        MonthlyStoryAdventureStep $step,
        MonthlyStoryAdventureService $adventureService,
        EntityManagerInterface $em,
        ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::StarKindred))
            throw new PSPNotUnlockedException('★Kindred');

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $playedStarKindred = UserQuestRepository::findOrCreate($em, $user, 'Played ★Kindred', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $playedStarKindred->getValue())
            throw new PSPInvalidOperationException('There\'s only time for one ★Kindred adventure per day. THEM\'S JUST THE RULES.');

        $playedStarKindred->setValue($today);

        if(InventoryService::countTotalInventory($em, $user, LocationEnum::HOME) > 150)
            throw new PSPInvalidOperationException('Your house is far too cluttered to play ★Kindred!');

        if($adventureService->isStepCompleted($user, $step))
            throw new PSPInvalidOperationException('You already completed that step!');

        if($step->getPreviousStep() && !$adventureService->isPreviousStepCompleted($user, $step))
            throw new PSPInvalidOperationException('You must have completed the previous step in the story!');

        $petIds = $request->request->all('pets');

        if(count($petIds) < $step->getMinPets() || count($petIds) > $step->getMaxPets())
        {
            if($step->getMinPets() == $step->getMaxPets())
                throw new PSPFormValidationException("Exactly {$step->getMinPets()} pets must go.");
            else
                throw new PSPFormValidationException("Between {$step->getMinPets()} and {$step->getMaxPets()} pets must go.");
        }

        $pets = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'id' => $petIds
        ]);

        if(count($pets) != count($petIds))
            throw new PSPPetNotFoundException();

        $message = $adventureService->completeStep($user, $step, $pets);

        $em->flush();

        return $responseService->success([
            'text' => $message
        ]);
    }
}