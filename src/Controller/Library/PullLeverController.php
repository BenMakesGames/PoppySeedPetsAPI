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

namespace App\Controller\Library;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/library")]
class PullLeverController
{
    #[Route("/pullLever", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getCurrent(
        UserAccessor $userAccessor,
        EntityManagerInterface $em,
        ResponseService $responseService,
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Library))
            throw new PSPNotUnlockedException('Library');

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::HollowEarth))
            throw new PSPInvalidOperationException('You\'ve already done this.');

        $bookCount = $em->createQueryBuilder()
            ->select('COUNT(i)')->from(Inventory::class, 'i')
            ->andWhere('i.user = :user')
            ->andWhere('i.location = :library')
            ->setParameter('user', $user)
            ->setParameter('library', LocationEnum::Library)
            ->getQuery()
            ->getSingleScalarResult();

        if($bookCount < 10)
        {
            $responseService->addFlashMessage('You pull the lever, but nothing happens... (Maybe you need more books? Is that how these secret library levers work??)');
        }
        else
        {
            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::HollowEarth);

            $responseService->addFlashMessage('You pull the lever, revealing a secret passage! (You can now travel to the Hollow Earth! Get some dice ready!)');
        }

        return $responseService->success();
    }
}
