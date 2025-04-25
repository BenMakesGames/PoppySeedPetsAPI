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


namespace App\Controller\Following;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Entity\UserFollowing;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/following")]
class UpdateNote
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{following}", methods: ["POST"])]
    public function handle(
        User $following, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $followingRecord = $em->getRepository(UserFollowing::class)->findOneBy([
            'user' => $user,
            'following' => $following,
        ]);

        if(!$followingRecord)
            throw new PSPNotFoundException('You\'re not following that person...');

        $note = $request->request->getString('note');

        if($note && \mb_strlen($note) > 255)
            throw new PSPFormValidationException('Note may not be longer than 255 characters. Sorry :(');

        $followingRecord->setNote($note);

        $em->flush();

        return $responseService->success();
    }
}
