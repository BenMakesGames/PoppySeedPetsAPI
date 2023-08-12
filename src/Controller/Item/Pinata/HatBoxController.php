<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\InventoryRepository;
use App\Repository\ItemGroupRepository;
use App\Repository\ItemRepository;
use App\Repository\PetRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/box")
 */
class HatBoxController extends AbstractController
{
    /**
     * @Route("/hat/{box}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openHatBox(
        Inventory $box, ResponseService $responseService, InventoryService $inventoryService, ItemRepository $itemRepository,
        UserStatsRepository $userStatsRepository, EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $box, 'box/hat/#/open');

        $location = $box->getLocation();
        $lockedToOwner = $box->getLockedToOwner();

        $hatItem = $itemRepository->findOneByName($squirrel3->rngNextFromArray([
            'Bright Top Hat',
            'Masquerade Mask',
            'Merchant\'s Cap',
            'Wizarding Hat',
            'Gray Bow',
            'Cool Sunglasses',
            'Sombrero',
            'Judy',
            'Propeller Beanie',
            'Crabhat',
            'Horsey Hat',
            'Dark Horsey Hat',
            'Eccentric Top Hat',
            'Pizzaface',
        ]));

        $userStatsRepository->incrementStat($user, 'Opened ' . $box->getItem()->getNameWithArticle());

        if($hatItem->getName() === 'Gray Bow')
        {
            $itemComment = 'Made out of the strap of ' . $box->getItem()->getNameWithArticle() . '.';
            $message = "You open the hat box... ta-da! It\'s... EMPTY?!?!\n\nRefusing to be outdone by a box, you tie the Hat Box\'s strap into a bow.";
        }
        else if($hatItem->getName() === 'Cool Sunglasses')
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s... ' . $hatItem->getNameWithArticle() . '? (Is that a hat?)';
        }
        else if($hatItem->getName() === 'Wings')
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s... two ' . $hatItem->getName() . '! (Which are each already two wings, so it\'s kinda\' like getting four, I guess?)';

            $inventoryService->receiveItem($hatItem, $user, $box->getCreatedBy(), $itemComment, $location, $lockedToOwner);
        }
        else
        {
            $itemComment = 'Found inside ' . $box->getItem()->getNameWithArticle() . '.';
            $message = 'You open the hat box... ta-da! It\'s ' . $hatItem->getNameWithArticle() . '!';
        }

        $inventoryService->receiveItem($hatItem, $user, $box->getCreatedBy(), $itemComment, $location, $lockedToOwner);

        $em->remove($box);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
