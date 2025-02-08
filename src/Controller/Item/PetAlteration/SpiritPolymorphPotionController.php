<?php
declare(strict_types=1);

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\SpiritCompanion;
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/spiritPolymorphPotion")]
class SpiritPolymorphPotionController extends AbstractController
{
    #[Route("/{inventory}/drink", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function drink(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'spiritPolymorphPotion');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->getSpiritCompanion())
            throw new PSPNotFoundException($pet->getName() . ' doesn\'t have a spirit companion! (Let\'s not waste a perfectly-good potion!)');

        $em->remove($inventory);

        $currentImage = $pet->getSpiritCompanion()->getImage();
        $possibleImages = array_filter(SpiritCompanion::IMAGES, fn($i) => $i !== $currentImage);

        $pet->getSpiritCompanion()->setImage($squirrel3->rngNextFromArray($possibleImages));

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
