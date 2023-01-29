<?php

namespace App\Controller\MonthlyStoryAdventure;

use App\Entity\MonthlyStoryAdventureStep;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\StoryAdventureTypeEnum;
use App\Repository\MonthlyStoryAdventureRepository;
use App\Repository\MonthlyStoryAdventureStepRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\MonthlyStoryAdventureService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/monthlyStoryAdventure")
 */
class GoOnAdventure extends AbstractController
{
    /**
     * @Route("/do/{step}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function handle(
        Request $request,
        MonthlyStoryAdventureStep $step,
        MonthlyStoryAdventureService $adventureService,
        PetRepository $petRepository,
        EntityManagerInterface $em,
        ResponseService $responseService,
        InventoryService $inventoryService,
        UserQuestRepository $userQuestRepository
    )
    {
        $user = $this->getUser();

        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $playedStarKindred = $userQuestRepository->findOrCreate($user, 'Played ★Kindred', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d'));

        if($today === $playedStarKindred->getValue())
            throw new UnprocessableEntityHttpException('There\'s only time for one ★Kindred adventure per day. THEM\'S JUST THE RULES.');

        $playedStarKindred->setValue($today);

        if($inventoryService->countTotalInventory($user, LocationEnum::HOME) > 150)
            throw new UnprocessableEntityHttpException('Your house is far too cluttered to play ★Kindred!');

        if($adventureService->isStepCompleted($user, $step))
            throw new UnprocessableEntityHttpException('You already completed that step!');

        if($step->getPreviousStep() && !$adventureService->isPreviousStepCompleted($user, $step))
            throw new UnprocessableEntityHttpException('You must have completed the previous step in the story!');

        $petIds = $request->request->get('pets');

        if(!is_array($petIds) || count($petIds) < $step->getMinPets() || count($petIds) > $step->getMaxPets())
        {
            if($step->getMinPets() == $step->getMaxPets())
                throw new UnprocessableEntityHttpException("Exactly {$step->getMinPets()} pets must go.");
            else
                throw new UnprocessableEntityHttpException("Between {$step->getMinPets()} and {$step->getMaxPets()} pets must go.");
        }

        $pets = $petRepository->findBy([
            'owner' => $user,
            'id' => $petIds
        ]);

        if(count($pets) != count($petIds))
            throw new NotFoundHttpException('One or more of the selected pets could not be found.');

        $message = $adventureService->completeStep($user, $step, $pets);

        $em->flush();

        return $responseService->success([
            'text' => $message
        ]);
    }
}