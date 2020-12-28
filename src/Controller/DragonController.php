<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Repository\DragonRepository;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dragon")
 */
class DragonController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getDragon(ResponseService $responseService, DragonRepository $dragonRepository)
    {
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t have an adult dragon!');

        return $responseService->success($dragon, SerializationGroupEnum::MY_DRAGON);
    }

    /**
     * @Route("offerings", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getOffer(
        ResponseService $responseService, DragonRepository $dragonRepository, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t have an adult dragon!');

        $treasures = $inventoryRepository->findTreasures($user);

        return $responseService->success($treasures, SerializationGroupEnum::DRAGON_TREASURE);
    }
}
