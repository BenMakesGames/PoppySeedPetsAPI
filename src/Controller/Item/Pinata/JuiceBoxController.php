<?php

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/juiceBox")
 */
class JuiceBoxController extends AbstractController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, Squirrel3 $rng, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'juiceBox/#/open');
        ItemControllerHelpers::validateHouseSpace($inventory, $inventoryService);

        $juiceBoxesOpened = $userStatsRepository->incrementStat($user, 'Juice Boxes Opened', 1)->getValue();

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $possibleJuices = [
            [ 'carrot', 'Carrot Juice' ],
            [ 'jellyfish' , 'Jellyfish Juice' ],
            [ 'orange', 'Orange Juice' ],
            [ 'pamplemousse', 'Pamplemousse Juice' ],
            [ 'red', 'Red Juice' ],
            [ 'green', 'Short Glass of Greenade' ],
            [ 'yellow', 'Tall Glass of Yellownade' ],
        ];

        if($juiceBoxesOpened % 13 == 0 || ($juiceBoxesOpened + 52) % 71 == 0 || ($juiceBoxesOpened + 107) % 112 == 0)
        {
            $juiceCount = $rng->rngNextInt(8, 12);

            $message = 'You poke the straw into the Juice Box, and take a si-- eh? Wait, the juice is just streaming out on its own, it-- uh... oh no! I-- it\'s not stopping! Why isn\'t it stopping?!' . "\r\n\r\n" . 'OH GOD! IT\'S STILL GOING! IT\'S GETTING, LIKE, LITERALLY EVERYWHERE! THE WALLS! YOUR SHIRT! OH, THE HUMANITY!' . "\r\n\r\n" . 'Eventually the box stops, and you clean up what amounts to ' . $juiceCount . ' glasses of juice.' . "\r\n\r\n" . 'Well. That was something.';

            for($i = 0; $i < $juiceCount - 1; $i++)
            {
                $juice = $rng->rngNextFromArray($possibleJuices);
                $inventoryService->receiveItem($juice[1], $user, $user, $user->getName() . ' got this from a wildly-full Juice Box.', $location, $lockedToOwner);
            }

            $inventoryService->receiveItem('Invisibility Juice', $user, $user, $user->getName() . ' got this from a wildly-full Juice Box.', $location, $lockedToOwner);
        }
        else
        {
            $rng->rngNextShuffle($possibleJuices);

            $inventoryService->receiveItem($possibleJuices[0][1], $user, $user, $user->getName() . ' got this from a Juice Box.', $location, $lockedToOwner);
            $inventoryService->receiveItem($possibleJuices[1][1], $user, $user, $user->getName() . ' got this from a Juice Box.', $location, $lockedToOwner);

            $message = 'You poke the straw into the Juice Box, and take a sip... mm! ' . ucfirst($possibleJuices[0][0]) . '-' . $possibleJuices[1][0] . ' flavor!' . "\r\n\r\n" . '(You got ' . $possibleJuices[0][1] . ' and ' . $possibleJuices[1][1] . '!)';
        }

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

}