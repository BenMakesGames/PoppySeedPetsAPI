<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\StoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/tuningFork")
 */
class TuningForkController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @throws \Exception
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        StoryService $storyService, Request $request
    )
    {
        $this->validateInventory($inventory, 'tuningFork/#/listen');

        $user = $this->getUser();

        $storyService->prepareStory($user, StoryEnum::SHARUMINYINKAS_DESPAIR);

        if($request->request->has('choice'))
        {
            $choice = trim($request->request->get('choice', ''));

            if($choice === '')
                throw new UnprocessableEntityHttpException('You didn\'t choose a choice!');

            $response = $storyService->makeChoice($choice);
        }
        else
            $response = $storyService->getStoryStep();

        $em->flush();

        return $responseService->success($response, SerializationGroupEnum::STORY);
    }
}