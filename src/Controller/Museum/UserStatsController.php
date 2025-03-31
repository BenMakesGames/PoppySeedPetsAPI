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


namespace App\Controller\Museum;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\Filter\ItemFilterService;
use App\Service\Filter\MuseumFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/museum")]
class UserStatsController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{user}/items", methods: ["GET"], requirements: ["user" => "\d+"])]
    public function userDonatedItems(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        if(!$this->getUser()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]
        );
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{user}/nonItems", methods: ["GET"], requirements: ["user" => "\d+"])]
    public function userNonDonatedItems(
        User $user,
        Request $request, ResponseService $responseService, ItemFilterService $itemFilterService
    )
    {
        if(!$this->getUser()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $itemFilterService->addRequiredFilter('notDonatedBy', $user->getId());

        return $responseService->success(
            $itemFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]
        );
    }

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{user}/itemCount", methods: ["GET"], requirements: ["user" => "\d+"])]
    public function userItemCount(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        if(!$this->getUser()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]
        );
    }
}
