<?php
namespace App\Controller\Dragon;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Repository\DragonRepository;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dragon")
 */
class GetPotentialOfferingsController extends AbstractController
{
    /**
     * @Route("/offerings", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getOfferings(
        ResponseService $responseService, DragonRepository $dragonRepository, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t have an adult dragon!');

        $treasures = $inventoryRepository->findTreasures($user);

        return $responseService->success($treasures, [ SerializationGroupEnum::DRAGON_TREASURE ]);
    }
}
