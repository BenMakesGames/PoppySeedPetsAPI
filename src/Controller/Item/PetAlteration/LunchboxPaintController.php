<?php
declare(strict_types=1);

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/lunchboxPaint")]
class LunchboxPaintController extends AbstractController
{
    #[Route("/{inventory}/paint", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function paintLunchbox(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'lunchboxPaint');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

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
