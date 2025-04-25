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


namespace App\Controller\Zoologist;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\Filter\PetSpeciesFilterService;
use App\Service\Filter\UserSpeciesCollectedFilterService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/zoologist")]
class GetDiscoveredSpeciesController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("", methods: ["GET"])]
    public function getDiscoveredSpecies(
        UserSpeciesCollectedFilterService $userSpeciesCollectedFilterService, 
        Request $request, 
        ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            throw new PSPNotUnlockedException('Zoologist');

        $userSpeciesCollectedFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $userSpeciesCollectedFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ZOOLOGIST_CATALOG ]
        );
    }
}