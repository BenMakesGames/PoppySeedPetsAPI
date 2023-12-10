<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\UserQuestRepository;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/flowerbomb")]
class FlowerbombController extends AbstractController
{
    #[Route("/{inventory}/toss", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $squirrel3,
        InventoryService $inventoryService, HotPotatoService $hotPotatoService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'flowerbomb/#/toss');

        $lastFlowerBombWasNarcissistic = UserQuestRepository::findOrCreate($em, $user, 'Last Flowerbomb was Narcissus', true);
        $numberOfTosses = HotPotatoService::countTosses($inventory);
        $isNarcissusBomb = $numberOfTosses === 0;

        if($isNarcissusBomb && $lastFlowerBombWasNarcissistic->getValue())
            return $hotPotatoService->tossItem($inventory);

        if($squirrel3->rngNextInt(1, 100) > 10 + $numberOfTosses * 5)
            return $hotPotatoService->tossItem($inventory);

        $possibleFlowers = $isNarcissusBomb
            ? [ 'Narcissus' ]
            : [
                'Agrimony',
                'Bird\'s-foot Trefoil',
                'Coriander Flower',
                'Green Carnation',
                'Iris',
                'Purple Violet',
                'Red Clover',
                'Viscaria',
                'Witch-hazel',
                'Wheat Flower',
                'Rice Flower',
            ]
        ;

        $lastFlowerBombWasNarcissistic->setValue($isNarcissusBomb);

        for($i = 0; $i < 10 + $numberOfTosses; $i++)
        {
            $flower = $squirrel3->rngNextFromArray($possibleFlowers);
            $inventoryService->receiveItem($flower, $user, $inventory->getCreatedBy(), 'This exploded out of a Flowerbomb.', $inventory->getLocation());
        }

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You get ready to toss the Flowerbomb, but it explodes in your hands! Flowers go flying everywhere! (Mostly into your house.)', [ 'itemDeleted' => true ]);
    }
}
