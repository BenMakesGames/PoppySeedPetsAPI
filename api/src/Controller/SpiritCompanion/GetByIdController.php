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

namespace App\Controller\SpiritCompanion;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\SpiritCompanion;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/spiritCompanion")]
class GetByIdController
{
    #[DoesNotRequireHouseHours]
    #[Route("/find/{spiritCompanion}", methods: ["GET"], requirements: ["spiritCompanion" => "\d+"])]
    public function find(SpiritCompanion $spiritCompanion, ResponseService $responseService): JsonResponse
    {
        return $responseService->success($spiritCompanion, [ SerializationGroupEnum::SPIRIT_COMPANION_PUBLIC_PROFILE ]);
    }
}
