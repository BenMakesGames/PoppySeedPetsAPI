<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\MeritEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class WonderlandTeaController extends PoppySeedPetsItemController
{
    /**
     * @Route("/tinyTea/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function serveTinyTea(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'tinyTea');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getScale() <= 50)
            throw new UnprocessableEntityHttpException($pet->getName() . ' can\'t get any smaller!');

        $pet->setScale(max(
            50,
            $pet->getScale() - $squirrel3->rngNextInt(8, 12)
        ));

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true, 'reloadPets' => true ]);
    }

    /**
     * @Route("/tremendousTea/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function serveTremendousTea(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'tremendousTea');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getScale() >= 150)
            throw new UnprocessableEntityHttpException($pet->getName() . ' can\'t get any bigger!');

        $pet->setScale(min(
            150,
            $pet->getScale() + $squirrel3->rngNextInt(8, 12)
        ));

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true, 'reloadPets' => true ]);
    }

    /**
     * @Route("/totallyTea/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function serveTotallyTea(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'totallyTea');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getScale() === 100)
            throw new UnprocessableEntityHttpException($pet->getName() . ' is already totally-normally sized.');

        $pet->setScale(100);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true, 'reloadPets' => true ]);
    }
}
