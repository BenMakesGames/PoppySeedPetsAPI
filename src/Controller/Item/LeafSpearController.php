<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class LeafSpearController extends PoppySeedPetsItemController
{
    /**
     * @Route("/leafSpear/{inventory}/unwrap", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unwrapLeafSpear(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'leafSpear/#/unwrap');

        $user = $this->getUser();

        $wasEquipped = $inventory->getHolder() !== null;

        $inventory->changeItem($itemRepository->findOneByName('Really Big Leaf'));

        $stringLocation = $inventory->getLocation() === LocationEnum::WARDROBE
            ? LocationEnum::HOME
            : $inventory->getLocation()
        ;

        $inventoryService->receiveItem('String', $user, $inventory->getCreatedBy(), $user->getName() . ' pulled this off of Leaf Spear.', $stringLocation, $inventory->getLockedToOwner());

        $em->flush();

        $responseService->setReloadPets($wasEquipped);

        return $responseService->itemActionSuccess('You untie the String, and the leaf practically unrolls on its own.', [ 'itemDeleted' => true ]);
    }
}
