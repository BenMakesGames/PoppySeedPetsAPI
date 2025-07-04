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

namespace App\Controller\StarKindred;

use App\Entity\MonthlyStoryAdventure;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use App\Service\StarKindred\StarKindredAdventureService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/starKindred")]
class RestartRemix
{
    #[Route("/restartRemix/{monthlyStoryAdventure}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function restartRemix(
        MonthlyStoryAdventure $monthlyStoryAdventure,
        UserAccessor $userAccessor,
        StarKindredAdventureService $starKindred,
        EntityManagerInterface $em,
        ResponseService $responseService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::StarKindred))
            throw new PSPNotUnlockedException('★Kindred');

        if(!$starKindred->userCanPlayREMIX($user))
            throw new PSPNotUnlockedException('★Kindred REMIX');

        if(!$monthlyStoryAdventure->isREMIX())
            throw new PSPInvalidOperationException('You can only restart a REMIX story.');

        $storyStepsCompleted = $em->getRepository(UserMonthlyStoryAdventureStepCompleted::class)
            ->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.adventureStep IN (:adventureSteps)')
            ->setParameter('user', $user)
            ->setParameter('adventureSteps', $monthlyStoryAdventure->getSteps())
            ->getQuery()
            ->execute();

        if(count($storyStepsCompleted) < $monthlyStoryAdventure->getSteps()->count())
            throw new PSPInvalidOperationException('You must complete all steps of a REMIX story before restarting it.');

        foreach($storyStepsCompleted as $storyStepCompleted) {
            $em->remove($storyStepCompleted);
        }

        return $responseService->success();
    }
}