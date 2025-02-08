<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/item/pspBirthdayPresent")]
class PSPBirthdayPresentController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pspBirthdayPresent/#/open');

        $location = $inventory->getLocation();

        $loot = [
            'Slice of Poppy Seed* Pie',
            $squirrel3->rngNextFromArray([
                'Ruby Feather', 'Secret Seashell', 'Candle', 'Gold Ring', 'Mysterious Seed',
                'Behatting Scroll', 'Magic Bean Milk', 'Magic Brush'
            ])
        ];

        $possibleLoot = [
            'Sweet Ginger Tea',
            'Coffee Jelly',
            'Caramel-covered Popcorn',
            'Cheese Ravioli',
            'Egg Salad',
            'Konpeitō',
            'Potato-mushroom Stuffed Onion',
        ];

        for($x = 0; $x < 2; $x++)
            $loot[] = $squirrel3->rngNextFromArray($possibleLoot);

        foreach($loot as $itemName)
        {
            $inventoryService->receiveItem(
                $itemName,
                $user,
                $user,
                $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '!',
                $location,
                $inventory->getLockedToOwner()
            );
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(
            'Inside the present, you found ' . ArrayFunctions::list_nice($loot) . '!',
            [ 'itemDeleted' => true ]
        );
    }
}
