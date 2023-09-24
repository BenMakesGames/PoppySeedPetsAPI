<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/magicBrush")
 */
class MagicBrushController extends AbstractController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function brush(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicBrush');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::SHEDS))
            throw new PSPInvalidOperationException($pet->getName() . ' already sheds!');

        $pet->addMerit(MeritRepository::findOneByName($em, MeritEnum::SHEDS));

        $item = $pet->getSpecies()->getSheds();

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this by brushing ' . $pet->getName() . ' with a Magic Brush.', LocationEnum::HOME, false);

        $em->remove($inventory);
        $em->flush();

        $plural = strtolower(mb_substr($item->getName(), -1, 1)) === 's';

        $responseService->addFlashMessage('You brush ' . $pet->getName() . ', and some ' . $item->getName() . ' ' . ($plural ? 'come' : 'comes') . ' off! (They now Shed!) Also, the magic brush breaks in half and disappears! (It wasn\'t your fault; Magic Brushes just be like that.)');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true, 'reloadInventory' => true ]);
    }
}
