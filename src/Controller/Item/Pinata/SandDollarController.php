<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/sandDollar")]
class SandDollarController extends AbstractController
{
    #[Route("/{inventory}/loot", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function lootSandDollar(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'sandDollar/#/loot');

        $transactionService->getMoney($user, 1, 'Found inside a Sand Dollar.');

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Silica Grounds', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
            ->setSpice($inventory->getSpice())
        ;

        if($squirrel3->rngNextInt(1, 10) === 1)
        {
            $inventoryService->receiveItem('String', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? Oh, it\'s a soggy bit of String. Well, it\'ll dry out.';
        }
        else if($squirrel3->rngNextInt(1, 10) === 1)
        {
            $inventoryService->receiveItem('Talon', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and hey: there\'s something else in here! It\'s a shark tooth, maybe? Or, like, a claw? Maybe a Talon? Let\'s go with Talon.';
        }
        else if($squirrel3->rngNextInt(1, 20) === 1)
        {
            $inventoryService->receiveItem('Mermaid Egg', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! There\'s something squishy! Ah! It\'s a Mermaid Egg!';
        }
        else if($squirrel3->rngNextInt(1, 20) === 1)
        {
            $inventoryService->receiveItem('Glowing Six-sided Die', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and hm, something... geometric? Ah: it\'s a die. And it\'s... glowing...';
        }
        else if($squirrel3->rngNextInt(1, 20) === 1)
        {
            $inventoryService->receiveItem('Plastic', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and what? There\'s some... Plastic in here?? That\'s kind of sad :| Well... one less piece in the ocean, I guess...';
        }
        else if($squirrel3->rngNextInt(1, 30) === 1)
        {
            $inventoryService->receiveItem('Secret Seashell', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? A Secret Seasheeeeeelllllll!';
        }
        else if($squirrel3->rngNextInt(1, 40) === 1)
        {
            $inventoryService->receiveItem('Cyan Bow', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location, $locked)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? Some kind of bright blue hair bow!';
        }
        else
        {
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds.';
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
