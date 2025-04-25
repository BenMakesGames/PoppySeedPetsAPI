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


namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\UserFilterService;
use App\Service\ResponseService;
use App\Service\Typeahead\UserTypeaheadService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class SearchController
{
    #[DoesNotRequireHouseHours]
    #[Route("/search", methods: ["GET"])]
    public function search(
        Request $request, UserFilterService $userFilterService, ResponseService $responseService
    ): JsonResponse
    {
        return $responseService->success(
            $userFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_PUBLIC_PROFILE ]
        );
    }

    #[DoesNotRequireHouseHours]
    #[Route("/typeahead", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, UserTypeaheadService $userTypeaheadService
    ): JsonResponse
    {
        $suggestions = $userTypeaheadService->search('name', $request->query->get('search', ''), 5);

        return $responseService->success($suggestions, [ SerializationGroupEnum::USER_TYPEAHEAD ]);
    }
}
