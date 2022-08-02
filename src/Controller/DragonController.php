<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Functions\RequestFunctions;
use App\Repository\DragonRepository;
use App\Repository\InventoryRepository;
use App\Service\DragonHostageService;
use App\Service\DragonService;
use App\Service\InventoryService;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET,
        ]);
    }

    /**
     * @Route("/offerings", methods={"GET"})
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

        return $responseService->success($treasures, [ SerializationGroupEnum::DRAGON_TREASURE ]);
    }

    /**
     * @Route("/giveTreasure", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveTreasure(
        ResponseService $responseService, DragonRepository $dragonRepository,
        Request $request, DragonService $dragonService
    )
    {
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

    /**
     * @Route("/dismissHostage", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function dismissHostage(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        DragonRepository $dragonRepository, DragonHostageService $dragonHostageService
    )
    {
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

    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService, DragonRepository $dragonRepository
    )
    {
        $user = $this->getUser();

        $petAssistantService->helpDragon($user, $pet);

        $em->flush();

        $dragon = $dragonRepository->findAdult($user);

        return $responseService->success($dragon, [
            SerializationGroupEnum::MY_DRAGON,
            SerializationGroupEnum::HELPER_PET
        ]);
    }

}
