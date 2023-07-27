<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/lunchboxPaint")
 */
class LunchboxPaintController extends AbstractController
{
    /**
     * @Route("/{inventory}/paint", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function paintLunchbox(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'lunchboxPaint');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $lunchboxIndex = $request->request->getInt('lunchboxIndex', -1);

        if($lunchboxIndex < 0 || $lunchboxIndex > 13)
            throw new PSPInvalidOperationException('Must select a new lunchbox design!');

        if($lunchboxIndex === $pet->getLunchboxIndex())
            throw new PSPInvalidOperationException('That\'s ' . $pet->getName() . '\'s current lunchbox design, already!');

        $em->remove($inventory);

        $pet->setLunchboxIndex($lunchboxIndex);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
