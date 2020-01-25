<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/sandDollar")
 */
class SandDollarController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/loot", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        $this->validateInventory($inventory, 'sandDollar/#/loot');

        $user = $this->getUser();

        $transactionService->getMoney($user, 1, 'Found inside a Sand Dollar.');

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Silica Grounds', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location);

        if(mt_rand(1, 10) === 1)
        {
            $inventoryService->receiveItem('String', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location);
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? Oh, it\'s a soggy bit of String. Well, it\'ll dry out.';
        }
        else if(mt_rand(1, 10) === 1)
        {
            $inventoryService->receiveItem('Talon', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location);
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and hey: there\'s something else in here! It\'s a shark tooth, maybe? Or, like, a claw? Maybe a Talon? Let\'s go with Talon.';
        }
        else if(mt_rand(1, 20) === 1)
        {
            $inventoryService->receiveItem('Mermaid Egg', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location);
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! There\'s something squishy! Ah! It\'s a Mermaid Egg!';
        }
        else if(mt_rand(1, 20) === 1)
        {
            $inventoryService->receiveItem('Glowing Six-sided Die', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location);
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and hm, something... geometric? Ah: it\'s a die. And it\'s... glowing...';
        }
        else if(mt_rand(1, 20) === 1)
        {
            $inventoryService->receiveItem('Plastic', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location);
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and what? There\'s some... Plastic in here?? That\'s kind of sad :| Well... one less piece in the ocean, I guess...';
        }
        else if(mt_rand(1, 50) === 1)
        {
            $inventoryService->receiveItem('Iron Key', $user, $user, $user->getName() . ' found this inside a Sand Dollar.', $location);
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds - and oh! What\'s this? Some kind of key! An Iron Key!';
        }
        else
        {
            $message = 'You rummage around inside the Sand Dollar. There was 1 dollar - er, I mean, moneys - and also a bit of sand - er, I mean, Silica Grounds.';
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}