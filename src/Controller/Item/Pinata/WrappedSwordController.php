<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\EnchantmentRepository;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/wrappedSword")
 */
class WrappedSwordController extends AbstractController
{
    /**
     * @Route("/{inventory}/unwrap", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unwrap(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        IRandom $rng, EntityManagerInterface $em, ItemRepository $itemRepository,
        SpiceRepository $spiceRepository, EnchantmentRepository $enchantmentRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wrappedSword/#/unwrap');

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $swordItem = $itemRepository->deprecatedFindOneByName($rng->rngNextFromArray([
            'Rapier',
            'Iron Sword',
            'Brute Force',
            $rng->rngNextFromArray([ 'Silver Keyblade', 'Gold Keyblade' ]),
            'Wooden Sword'
        ]));

        $sword = $inventoryService->receiveItem($swordItem, $user, $user, $user->getName() . ' unwrapped a Wrapped Sword, revealing this!', $location, $lockedToOwner);
        $inventoryService->receiveItem('White Cloth', $user, $user, $user->getName() . ' unwrapped a Wrapped Sword; this was the wrapping.', $location, $lockedToOwner);

        if($sword->getSpice() == null && $rng->rngNextBool())
        {
            $spice = $spiceRepository->findOneByName($rng->rngNextFromArray([
                'Spicy',
                'Ducky',
                'Nutmeg-laden',
                'Tropical',
                'Buttery',
                'Grape?',
                'Rain-scented',
                'Juniper',
            ]));

            $sword->setSpice($spice);
        }
        else
        {
            $bonus = $enchantmentRepository->findOneByName($rng->rngNextFromArray([
                'Bright',
                'Spider\'s',
                'of the Moon',
                'Enchantress\'s',
                'Explosive', // firework
                'Bezeling',
                'Climbing', // dragon vase
                'Dancing',
                'Fisherman\'s',
                'Fluffmonger\'s',
                'Glowing',
                'of the Unicorn',
            ]));

            $sword->setEnchantment($bonus);
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You unwrap the wrapped sword... it\'s ' . $sword->getItem()->getNameWithArticle() . '! (You keep the cloth, too, of course!)', [ 'itemDeleted' => true ]);
    }
}
