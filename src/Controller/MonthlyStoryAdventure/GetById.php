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


namespace App\Controller\MonthlyStoryAdventure;

use App\Entity\MonthlyStoryAdventure;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\UserQuestRepository;
use App\Repository\MonthlyStoryAdventureStepRepository;
use App\Repository\UserMonthlyStoryAdventureStepCompletedRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/monthlyStoryAdventure")]
class GetById extends AbstractController
{
    #[Route("/{story}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function handle(
        MonthlyStoryAdventure $story,
        MonthlyStoryAdventureStepRepository $monthlyStoryAdventureStepRepository,
        UserMonthlyStoryAdventureStepCompletedRepository $userMonthlyStoryAdventureStepCompletedRepository,
        ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::StarKindred))
            throw new PSPNotUnlockedException('★Kindred');

        $complete = $userMonthlyStoryAdventureStepCompletedRepository->findComplete($user, $story);
        $available = $monthlyStoryAdventureStepRepository->findAvailable($story, $complete);
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
}