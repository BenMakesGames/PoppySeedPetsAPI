<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Service\ResponseService;
use App\Service\StoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/tuningFork")]
class TuningForkController extends AbstractController
{
    #[Route("/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        StoryService $storyService, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tuningFork/#/listen');

        $response = $storyService->doStory($user, StoryEnum::SHARUMINYINKAS_DESPAIR, $request->request, $inventory);

        return $responseService->success($response, [ SerializationGroupEnum::STORY ]);
    }
}
