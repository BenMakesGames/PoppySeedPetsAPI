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


namespace App\Controller\PetActivityLogs;

use App\Enum\SerializationGroupEnum;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/petActivityLogs")]
class SearchController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("", methods: ["GET"])]
    public function history(
        Request $request, ResponseService $responseService, PetActivityLogsFilterService $petActivityLogsFilterService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $petActivityLogsFilterService->addRequiredFilter('user', $user->getId());

        $logs = $petActivityLogsFilterService->getResults($request->query);

        return $responseService->success($logs, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::PET_ACTIVITY_LOGS_AND_PUBLIC_PET
        ]);
    }
}
