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
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ArrayFunctions;
use App\Service\Filter\PetFilterService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/pet")]
class GetDaycareController
{
    #[Route("/daycare", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyDaycarePets(
        ResponseService $responseService, PetFilterService $petFilterService, Request $request,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $petFilterService->addRequiredFilter('owner', $user->getId());
        $petFilterService->addRequiredFilter('location', [ PetLocationEnum::DAYCARE, PetLocationEnum::HOME ]);

        $petsInDaycare = $petFilterService->getResults($request->query);

        return $responseService->success(
            $petsInDaycare,
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_PET ]
        );
    }
}
