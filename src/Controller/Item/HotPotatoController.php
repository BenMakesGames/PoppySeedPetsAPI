<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\UserRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/hotPotato")
 */
class HotPotatoController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/toss", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function toss(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserRepository $userRepository, InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'hotPotato/#/toss');

        $user = $this->getUser();

        if(mt_rand(1, 5) === 1)
        {
            $inventoryService->receiveItem('Smashed Potatoes', $user, $inventory->getCreatedBy(), 'The remains of an exploded Hot Potato.', $inventory->getLocation());
            $inventoryService->receiveItem('Liquid-hot Magma', $user, $inventory->getCreatedBy(), 'The remains of an exploded Hot Potato.', $inventory->getLocation());

            $thirdItem = ArrayFunctions::pick_one([
                'Butter',
                'Charcoal',
                'Oil',
                'Glowing Six-sided Die',
                'Sour Cream',
            ]);

            $inventoryService->receiveItem($thirdItem, $user, $inventory->getCreatedBy(), 'This exploded out of a Hot Potato.', $inventory->getLocation());

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Hot Potato, but it explodes in your hands! It\'s a bit hot, but hey: you got Smashed Potatoes, Liquid-hot Magma, and ' . $thirdItem . '!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        }
        else
        {
            $target = $userRepository->findOneRecentlyActive($user);

            if($target === null)
                return $responseService->itemActionSuccess('Hm... there\'s no one to toss it, to! (I guess no one\'s been playing Poppy Seed Pets...)');

            $inventory
                ->setOwner($target)
                ->addComment($user->getName() . ' tossed this to you!')
                ->setModifiedOn()
                ->setSellPrice(null)
            ;

            $em->flush();

            return $responseService->itemActionSuccess('You toss the Hot Potato to <a href="/poppyopedia/directory/' . $target->getId() . '">' . $target->getName() . '</a>!', [ 'itemDeleted' => true ]);
        }
    }
}