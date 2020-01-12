<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/cursedScissors")
 */
class CursedScissorsController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/cut", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function forgetRelationship(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'cursedScissors');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        $otherPetId = $request->request->getInt('otherPet', 0);
        $otherPet = $petRepository->find($otherPetId);

        if(!$otherPet)
            throw new UnprocessableEntityHttpException('Did you forget to select a pet to forget? It seems like you forgot to select a pet to forget.');

        $relationship = $pet->getRelationshipWith($otherPet);

        if(!$relationship)
            throw new UnprocessableEntityHttpException($pet->getName() . ' and ' . $otherPet->getName() . ' already don\'t know each other...');

        $em->remove($relationship);
        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
