<?php

namespace App\Controller\MonthlyStoryAdventure;

use App\Entity\MonthlyStoryAdventure;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\MonthlyStoryAdventureRepository;
use App\Repository\MonthlyStoryAdventureStepRepository;
use App\Repository\UserMonthlyStoryAdventureStepCompletedRepository;
use App\Repository\UserQuestRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/monthlyStoryAdventure")
 */
class GetById extends AbstractController
{
    /**
     * @Route("/{story}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function handle(
        MonthlyStoryAdventure $story,
        MonthlyStoryAdventureStepRepository $monthlyStoryAdventureStepRepository,
        UserMonthlyStoryAdventureStepCompletedRepository $userMonthlyStoryAdventureStepCompletedRepository,
        ResponseService $responseService, UserQuestRepository $userQuestRepository
    )

    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::StarKindred))
            throw new PSPNotUnlockedException('★Kindred');

        $complete = $userMonthlyStoryAdventureStepCompletedRepository->findComplete($user, $story);
        $available = $monthlyStoryAdventureStepRepository->findAvailable($story, $complete);
        $playedStarKindred = $userQuestRepository->findOrCreate($user, 'Played ★Kindred', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

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