<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\UserQuestRepository;
use App\Service\HotPotatoService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/flowerbomb")
 */
class FlowerbombController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/toss", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        InventoryService $inventoryService, UserQuestRepository $userQuestRepository, HotPotatoService $hotPotatoService
    )
    {
        $this->validateInventory($inventory, 'flowerbomb/#/toss');

        $user = $this->getUser();

        $lastFlowerBombWasNarcissistic = $userQuestRepository->findOrCreate($user, 'Last Flowerbomb was Narcissus', true);

        $numberOfTosses = $hotPotatoService->countTosses($inventory);

        if($numberOfTosses === 0)
        {
            $chanceToExplode = $lastFlowerBombWasNarcissistic->getValue() ? 0 : 10;

            $possibleFlowers = [
                'Narcissus'
            ];
        }
        else
        {
            $chanceToExplode = 20;

            $possibleFlowers = [
                'Agrimony',
                'Bird\'s-foot Trefoil',
                'Coriander Flower',
                'Green Carnation',
                'Iris',
                'Purple Violet',
                'Red Clover',
                'Viscaria',
                'Witch-hazel',
                'Wheat',
            ];
        }

        $explodes = $squirrel3->rngNextInt(1, 100) <= $chanceToExplode;

        $lastFlowerBombWasNarcissistic->setValue($explodes && $numberOfTosses === 0);

        if($explodes)
        {
            for($i = 0; $i < 10 + $numberOfTosses; $i++)
            {
                $flower = $squirrel3->rngNextFromArray($possibleFlowers);
                $inventoryService->receiveItem($flower, $user, $inventory->getCreatedBy(), 'This exploded out of a Flowerbomb.', $inventory->getLocation());
            }

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Flowerbomb, but it explodes in your hands! Flowers go flying everywhere! (Mostly into your house.)', [ 'itemDeleted' => true ]);
        }
        else
        {
            return $hotPotatoService->tossItem($inventory);
        }
    }
}
