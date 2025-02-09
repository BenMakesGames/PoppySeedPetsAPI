<?php
declare(strict_types=1);

namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/tellSamarzhoustianDelights")]
class TellSamarzhoustianScrollController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $squirrel3, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'tellSamarzhoustianDelights/#/open');

        $ingredients = [
            'Algae',
            'Celery',
            'Corn',
            'Jellyfish Jelly',
            'Noodles',
            'Onion',
            'Seaweed',
        ];

        $spices = [
            'Nutmeg',
            'Onion Powder',
            'Spicy Spice',
            'Duck Sauce',
        ];

        $fancyItems = [
            'Everlasting Syllabub',
            'Bizet Cake',
            'Chili Calamari',
            'Mushroom Broccoli Krahi',
            'Poutine',
            'Qatayef',
            'Red Cobbler',
            'Shakshouka',
            'Tentacle Fried Rice',
            'Tentacle Onigiri',
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        $listOfItems = [
            $squirrel3->rngNextFromArray($ingredients),
            $squirrel3->rngNextFromArray($spices),
            $squirrel3->rngNextFromArray($fancyItems),
        ];

        foreach($listOfItems as $itemName)
        {
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->flush();

        return $responseService->itemActionSuccess('You read the scroll, producing ' . ArrayFunctions::list_nice($listOfItems) . '!', [ 'itemDeleted' => true ]);
    }
}
