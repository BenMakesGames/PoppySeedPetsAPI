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
use App\Entity\MonthlyStoryAdventureStep;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\UserQuestRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/starKindred")]
class GetById
{
    #[Route("/{story}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function handle(
        MonthlyStoryAdventure $story, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::StarKindred))
            throw new PSPNotUnlockedException('★Kindred');

        $complete = $em->getRepository(UserMonthlyStoryAdventureStepCompleted::class)
            ->createQueryBuilder('c')
            ->join('c.adventureStep', 's')
            ->andWhere('c.user = :user')
            ->andWhere('s.adventure = :adventure')
            ->setParameter('user', $user)
            ->setParameter('adventure', $story)
            ->getQuery()
            ->execute();

        $available = self::findAvailable($em, $story, $complete);
        $playedStarKindred = UserQuestRepository::findOrCreate($em, $user, 'Played ★Kindred', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        $canNextPlayOn = \DateTimeImmutable::createFromFormat('Y-m-d', $playedStarKindred->getValue())->add(\DateInterval::createFromDateString('1 day'));

        return $responseService->success(
            [
                'story' => $story,
                'stepsAvailable' => $available,
                'stepsComplete' => $complete,
                'canNextPlayOn' => $canNextPlayOn->format('Y-m-d')
            ],
            [
                SerializationGroupEnum::STAR_KINDRED_STORY_DETAILS,
                SerializationGroupEnum::STAR_KINDRED_STORY_STEP_AVAILABLE,
                SerializationGroupEnum::STAR_KINDRED_STORY_STEP_COMPLETE
            ]
        );
    }

    /**
     * @param UserMonthlyStoryAdventureStepCompleted[] $completed
     * @return MonthlyStoryAdventureStep[]
     */
    private static function findAvailable(EntityManagerInterface $em, MonthlyStoryAdventure $adventure, array $completed): array
    {
        $qb = $em->getRepository(MonthlyStoryAdventureStep::class)->createQueryBuilder('s');

        $completedSteps = array_map(fn(UserMonthlyStoryAdventureStepCompleted $c) => $c->getAdventureStep()->getStep(), $completed);
        $completedAdventureStepIds = array_map(fn(UserMonthlyStoryAdventureStepCompleted $c) => $c->getAdventureStep()->getId(), $completed);

        $qb = $qb
            ->andWhere('s.adventure = :adventure')
            ->setParameter('adventure', $adventure)
        ;

        if(count($completedSteps) > 0)
        {
            $qb = $qb
                ->andWhere($qb->expr()->orX('s.previousStep IS NULL', 's.previousStep IN (:completedSteps)'))
                ->setParameter('completedSteps', $completedSteps)
            ;
        }
        else
        {
            $qb = $qb->andWhere('s.previousStep IS NULL');
        }

        if(count($completedAdventureStepIds) > 0)
        {
            $qb = $qb
                ->andWhere('s.id NOT IN (:completedAdventureStepIds)')
                ->setParameter('completedAdventureStepIds', $completedAdventureStepIds)
            ;
        }

        return $qb->getQuery()->execute();
    }

}