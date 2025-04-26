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
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/following")]
class FollowController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("", methods: ["POST"])]
    public function add(
        Request $request, ResponseService $responseService,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $followingId = $request->request->getInt('following');
        $note = $request->request->getString('note');

        if($followingId === $user->getId())
            throw new PSPInvalidOperationException('You can\'t follow yourself! That\'s so RANDOM! You\'re so RANDOM!');

        $following = $em->getRepository(User::class)->find($followingId);

        if(!$following)
            throw new PSPNotFoundException('Could not find a person with that number.');

        $alreadyFollowing = $em->getRepository(UserFollowing::class)->findOneBy([
            'user' => $user,
            'following' => $following
        ]);

        if($alreadyFollowing)
            throw new PSPInvalidOperationException('You\'re already following that person.');

        $newFriend = (new UserFollowing())
            ->setUser($user)
            ->setFollowing($following)
            ->setNote($note)
        ;

        $em->persist($newFriend);

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::Florist);

        $em->flush();

        return $responseService->success();
    }
}
