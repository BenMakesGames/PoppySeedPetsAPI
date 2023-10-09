<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/cursedScissors")
 */
class CursedScissorsController extends AbstractController
{
    /**
     * @Route("/{inventory}/cut", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function forgetRelationship(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cursedScissors');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $otherPetId = $request->request->getInt('otherPet', 0);
        $otherPet = $em->getRepository(Pet::class)->find($otherPetId);

        if(!$otherPet)
            throw new PSPFormValidationException('Did you forget to select a pet to forget? It seems like you forgot to select a pet to forget.');

        $relationship = $pet->getRelationshipWith($otherPet);

        if(!$relationship)
            throw new PSPInvalidOperationException($pet->getName() . ' and ' . $otherPet->getName() . ' already don\'t know each other...');

        $em->remove($relationship);
        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
