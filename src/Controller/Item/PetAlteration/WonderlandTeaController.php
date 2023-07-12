<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class WonderlandTeaController extends AbstractController
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

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'tinyTea');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getScale() <= 50)
            throw new PSPInvalidOperationException($pet->getName() . ' can\'t get any smaller!');

        $pet->setScale(max(
            50,
            $pet->getScale() - $squirrel3->rngNextInt(8, 12)
        ));

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadPets(true);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
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

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'tremendousTea');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getScale() >= 150)
            throw new PSPInvalidOperationException($pet->getName() . ' can\'t get any bigger!');

        $pet->setScale(min(
            150,
            $pet->getScale() + $squirrel3->rngNextInt(8, 12)
        ));

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadPets(true);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
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

        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'totallyTea');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getScale() === 100)
            throw new PSPInvalidOperationException($pet->getName() . ' is already totally-normally sized.');

        $pet->setScale(100);

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadPets(true);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
