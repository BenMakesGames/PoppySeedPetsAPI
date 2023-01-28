<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Service\ResponseService;
use App\Service\StoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        StoryService $storyService, Request $request
    )
    {
        $this->validateInventory($inventory, 'tuningFork/#/listen');

        $user = $this->getUser();

        $response = $storyService->doStory($user, StoryEnum::SHARUMINYINKAS_DESPAIR, $request->request, $inventory);

        return $responseService->success($response, [ SerializationGroupEnum::STORY ]);
    }
}
