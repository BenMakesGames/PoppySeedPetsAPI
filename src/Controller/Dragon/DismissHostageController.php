<?php
namespace App\Controller\Dragon;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\DragonRepository;
use App\Service\DragonHostageService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dragon")
 */
class DismissHostageController extends AbstractController
{
    /**
     * @Route("/dismissHostage", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function dismissHostage(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        DragonRepository $dragonRepository, DragonHostageService $dragonHostageService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $dragon = $dragonRepository->findAdult($user);

        if(!$dragon || !$dragon->getHostage())
            throw new NotFoundHttpException('You don\'t have a dragon hostage...');

        $hostage = $dragon->getHostage();

        $loot = $dragonHostageService->generateLoot($hostage->getType());

        $em->remove($hostage);
        $dragon->setHostage(null);

        $responseService->addFlashMessage($loot->flashMessage);

        $inventoryService->receiveItem($loot->item, $dragon->getOwner(), $dragon->getOwner(), $loot->comment, LocationEnum::HOME, false);

        $em->flush();

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET
        ]);
    }
}
