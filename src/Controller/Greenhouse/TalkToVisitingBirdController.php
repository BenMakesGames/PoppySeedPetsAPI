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


namespace App\Controller\Greenhouse;

use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/greenhouse")]
class TalkToVisitingBirdController
{
    #[Route("/talkToVisitingBird", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function talkToBird(
        ResponseService $responseService, EntityManagerInterface $em, GreenhouseService $greenhouseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        if(!$user->getGreenhouse()->getVisitingBird())
            throw new PSPNotFoundException('Hm... there\'s no bird here. Reload, maybe??');

        $message = $greenhouseService->approachBird($user->getGreenhouse());

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->success();
    }
}
