<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\TradesUnlocked;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Functions\ArrayFunctions;
use App\Model\StoryStep;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StoryService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/merchantFish")
 */
class MerchantFishController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/talk", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        StoryService $storyService, Request $request, TraderService $traderService, InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'merchantFish/#/talk');

        $user = $this->getUser();

        if($user->getUnlockedTrader() === null)
        {
            $response = $storyService->doStory($user, StoryEnum::MERCHANT_FISH_MERCHANT, $request->request, $inventory);

            return $responseService->success($response, SerializationGroupEnum::STORY);
        }
        else
        {
            $em->remove($inventory);

            $responseService->setReloadInventory();

            $lockedTradeGroups = $traderService->getLockedTradeGroups($user);

            if(count($lockedTradeGroups) === 0)
            {
                $loot = $squirrel3->rngNextFromArray([
                    $squirrel3->rngNextFromArray([ 'Yellow Dye', 'Green Dye' ]),
                    $squirrel3->rngNextFromArray([ 'Spicy Spice', 'Nutmeg' ]),
                    $squirrel3->rngNextFromArray([ 'Silver Bar', 'Gold Bar' ]),
                    'Tentacle',
                    'White Cloth',
                    'Secret Seashell'
                ]);

                $message = 'You return the Merchant Fish to the Nation-state of Tell Samarzhoustia, who give you ' . $loot . ' as thanks. Also, they let you keep the fish bowl.';

                $inventoryService->receiveItem($loot, $user, $user, 'Received from Tell Samarzhoustia as thanks for a Merchant Fish.', $inventory->getLocation(), $inventory->getLockedToOwner());
                $inventoryService->receiveItem('Crystal Ball', $user, $user, 'This Crystal Ball was once acting as a fish bowl for a Merchant Fish.', $inventory->getLocation(), $inventory->getLockedToOwner());
            }
            else
            {
                $newTrades = (new TradesUnlocked())
                    ->setUser($user)
                    ->setTrades($squirrel3->rngNextFromArray($lockedTradeGroups))
                ;

                $em->persist($newTrades);

                $message = 'You return the Merchant Fish to the Nation-state of Tell Samarzhoustia, who expand their trading offers as thanks. Also, they let you keep the fish bowl.';

                $inventoryService->receiveItem('Crystal Ball', $user, $user, 'This Crystal Ball was once acting as a fish bowl for a Merchant Fish.', $inventory->getLocation(), $inventory->getLockedToOwner());
            }

            $storyStep = new StoryStep();

            $storyStep->storyTitle = 'Merchant Fish Merchant';
            $storyStep->style = 'description';
            $storyStep->background = null;
            $storyStep->image = null;
            $storyStep->content = $message;
            $storyStep->choices = [];

            $em->flush();

            $responseService->setReloadInventory();

            return $responseService->success($storyStep, [ SerializationGroupEnum::STORY ]);
        }
    }
}
