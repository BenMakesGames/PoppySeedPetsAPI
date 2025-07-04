<?php
declare(strict_types=1);

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

#[Route("/starKindred")]
class RestartRemix
{
    #[Route("/restartRemix/{monthlyStoryAdventure}", methods: ["POST"])]
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