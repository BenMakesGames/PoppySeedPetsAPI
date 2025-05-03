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


namespace App\Controller;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\DeviceStats;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/deviceStats")]
class PutController
{
    #[DoesNotRequireHouseHours]
    #[Route("", methods: ["PUT"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function create(
        ResponseService $responseService,
        #[MapRequestPayload] DeviceStatsRequest $dto,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($dto->userAgent && $dto->language && $dto->windowWidth && $dto->screenWidth)
        {
            $deviceStats = (new DeviceStats())
                ->setUser($user)
                ->setUserAgent($dto->userAgent)
                ->setLanguage($dto->language)
                ->setTouchPoints($dto->touchPoints ?? 0)
                ->setWindowWidth($dto->windowWidth)
                ->setScreenWidth($dto->screenWidth)
            ;

            $em->persist($deviceStats);
            $em->flush();
        }

        return $responseService->success();
    }
}

class DeviceStatsRequest
{
    public function __construct(
        public readonly ?string $userAgent,
        public readonly ?string $language,
        public readonly ?int $touchPoints,
        public readonly ?int $windowWidth,
        public readonly ?int $screenWidth,
    )
    {
    }
}
