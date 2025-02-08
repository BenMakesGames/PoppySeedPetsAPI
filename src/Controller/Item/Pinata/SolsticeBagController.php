<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class SolsticeBagController extends AbstractController
{
    #[Route("/summerSolsticeBag/{bag}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openSummerSolsticeBag(
        Inventory $bag, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, HattierService $hattierService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $bag, 'summerSolsticeBag/#/open');
        ItemControllerHelpers::validateLocationSpace($bag, $em);

        $location = $bag->getLocation();
        $lockedToOwner = $bag->getLockedToOwner();

        $possibleItems = [
            'Hot Dog', 'Grilled Fish', 'Melowatern', 'Blackberry Ice Cream', 'Rainbow', 'Bug-catcher\'s Net',
            'Orange Sportsball Ball', 'Sunflower', 'Tile: Private Fishing Spot', 'Tall Glass of Yellownade'
        ];

        $items = $squirrel3->rngNextSubsetFromArray($possibleItems, 3);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, "Found inside {$bag->getItem()->getNameWithArticle()}.", $location, $lockedToOwner);

        $em->remove($bag);

        $em->flush();

        $message = 'You rummaged around the bag, and found ' . ArrayFunctions::list_nice($items) . '!';

        $auraEnchantment = EnchantmentRepository::findOneByName($em, 'Summer\'s');

        if(!$hattierService->userHasUnlocked($user, $auraEnchantment))
        {
            $hattierService->playerUnlockAura($user, $auraEnchantment, "You unlocked this by opening {$bag->getItem()->getNameWithArticle()}!");

            $message .= "\n\nAnd oh! There's a summer aura inside!? (Apparently that's possible?!?!)";

            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Hattier))
                $message .= ' You\'re not sure what to do with the thing, at present... maybe it\'ll come in handy later? (You haven\'t unlocked this feature of the game yet... but when you do, the summer aura will be available to you!)';
        }

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/winterSolsticeBag/{bag}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openWinterSolsticeBag(
        Inventory $bag, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, HattierService $hattierService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $bag, 'winterSolsticeBag/#/open');
        ItemControllerHelpers::validateLocationSpace($bag, $em);

        $location = $bag->getLocation();
        $lockedToOwner = $bag->getLockedToOwner();

        $possibleItems = [
            'Eggnog', 'Pinecone', 'Shortbread Cookies', 'Plastic Shovel', 'A Single Candy Cane',
            'Marshmallows', 'Mint', 'Sweet Ginger Tea', 'Tile: Bakery Bites'
        ];

        $items = $squirrel3->rngNextSubsetFromArray($possibleItems, 3);

        foreach($items as $item)
            $inventoryService->receiveItem($item, $user, $user, "Found inside {$bag->getItem()->getNameWithArticle()}.", $location, $lockedToOwner);

        $em->remove($bag);

        $em->flush();

        $message = 'You rummaged around the bag, and found ' . ArrayFunctions::list_nice($items) . '!';

        $auraEnchantment = EnchantmentRepository::findOneByName($em, 'Winter\'s');

        if(!$hattierService->userHasUnlocked($user, $auraEnchantment))
        {
            $hattierService->playerUnlockAura($user, $auraEnchantment, "You unlocked this by opening {$bag->getItem()->getNameWithArticle()}!");

            $message .= "\n\nAnd oh! There's a winter aura inside!? (Apparently that's possible?!?!)";

            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Hattier))
                $message .= ' You\'re not sure what to do with the thing, at present... maybe it\'ll come in handy later? (You haven\'t unlocked this feature of the game yet... but when you do, the winter aura will be available to you!)';
        }

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
