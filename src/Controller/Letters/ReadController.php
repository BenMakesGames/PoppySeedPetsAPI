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


namespace App\Controller\Letters;

use App\Entity\User;
use App\Entity\UserLetter;
use App\Exceptions\PSPNotFoundException;
use App\Service\FieldGuideService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/letter")]
class ReadController
{
    #[Route("/{letter}/read", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function markRead(
        UserLetter $letter, EntityManagerInterface $em, ResponseService $responseService,
        FieldGuideService $fieldGuideService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($letter->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('That letter does not exist??!?');

        $letter->setIsRead();

        if($letter->getLetter()->getFieldGuideEntry())
        {
            $fieldGuideService->maybeUnlock(
                $user,
                $letter->getLetter()->getFieldGuideEntry(),
                '%user:' . $user->getId() . '.Name% read a letter from ' . $letter->getLetter()->getSender() . '.'
            );
        }

        $em->flush();

        return $responseService->success();
    }
}
