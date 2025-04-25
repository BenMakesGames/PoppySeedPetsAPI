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


namespace App\Controller\Beehive;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\BeehiveService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/beehive")]
class FeedController
{
    #[Route("/feed", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function feedItem(
        ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryService $inventoryService, Request $request, UserStatsService $userStatsRepository,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive) || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $beehive = $user->getBeehive();

        if($beehive->getFlowerPower() > 0)
            throw new PSPInvalidOperationException('The colony is still working on the last item you gave them.');

        $alternate = $request->request->getBoolean('alternate');

        $itemToFeed = $alternate
            ? $beehive->getAlternateRequestedItem()
            : $beehive->getRequestedItem()
        ;

        if($inventoryService->loseItem($user, $itemToFeed->getId(), LocationEnum::HOME, 1) === 0)
        {
            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
                throw new PSPNotFoundException('You do not have ' . $itemToFeed->getNameWithArticle() . ' in your house!');

            if($inventoryService->loseItem($user, $itemToFeed->getId(), LocationEnum::BASEMENT, 1) === 0)
                throw new PSPNotFoundException('You do not have ' . $itemToFeed->getNameWithArticle() . ' in your house, or your basement!');
            else
                $responseService->addFlashMessage('You give the queen ' . $itemToFeed->getNameWithArticle() . ' from your basement. Her bees immediately whisk it away into the hive!');
        }
        else
            $responseService->addFlashMessage('You give the queen ' . $itemToFeed->getNameWithArticle() . ' from your house. Her bees immediately whisk it away into the hive!');

        $beehiveService->fedRequestedItem($beehive, $alternate);
        $beehive->setInteractionPower();

        $userStatsRepository->incrementStat($user, UserStatEnum::FED_THE_BEEHIVE);

        $em->flush();

        return $responseService->success($beehive, [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
