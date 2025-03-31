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
use App\Entity\User;
use App\Entity\UserActivityLogTag;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\UserActivityLogsFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/userActivityLogs")]
class UserActivityLogsController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function history(
        Request $request, ResponseService $responseService, UserActivityLogsFilterService $userActivityLogsFilterService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $userActivityLogsFilterService->addRequiredFilter('user', $user->getId());

        $logs = $userActivityLogsFilterService->getResults($request->query);

        return $responseService->success($logs, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_ACTIVITY_LOGS ]);
    }

    #[DoesNotRequireHouseHours]
    #[Route("/getAllTags", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAllTags(ResponseService $responseService, EntityManagerInterface $em)
    {
        $tags = $em->getRepository(UserActivityLogTag::class)->findAll();

        return $responseService->success($tags, [ SerializationGroupEnum::USER_ACTIVITY_LOGS ]);
    }
}
