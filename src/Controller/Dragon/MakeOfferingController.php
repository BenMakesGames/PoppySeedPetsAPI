<?php
namespace App\Controller\Dragon;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Functions\RequestFunctions;
use App\Repository\DragonRepository;
use App\Service\DragonService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dragon")
 */
class MakeOfferingController extends AbstractController
{
    /**
     * @Route("/giveTreasure", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveTreasure(
        ResponseService $responseService, DragonRepository $dragonRepository,
        Request $request, DragonService $dragonService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $itemIds = RequestFunctions::getUniqueIdsOrThrow($request, 'treasure', 'No items were selected to give???');

        $message = $dragonService->giveTreasures($user, $itemIds);

        $responseService->addFlashMessage($message);

        $dragon = $dragonRepository->findAdult($user);

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET,
        ]);
    }
}
