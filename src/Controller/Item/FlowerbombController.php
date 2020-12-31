<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
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
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserRepository $userRepository, InventoryService $inventoryService
    )
    {
        $this->validateInventory($inventory, 'flowerbomb/#/toss');

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

        $user = $this->getUser();

        $numberOfTosses = 0;

        foreach($inventory->getComments() as $comment)
        {
            if(strpos($comment, ' tossed this to ') !== false)
                $numberOfTosses++;
        }

        if($numberOfTosses === 0)
        {
            $possibleFlowers = ['Narcissus'];
            $percentChanceToExplode = 10;
        }
        else
        {
            $percentChanceToExplode = 20;
        }

        if(mt_rand(1, 100) <= $percentChanceToExplode)
        {
            for($i = 0; $i < 10 + $numberOfTosses; $i++)
            {
                $flower = ArrayFunctions::pick_one($possibleFlowers);
                $inventoryService->receiveItem($flower, $user, $inventory->getCreatedBy(), 'This exploded out of a Flowerbomb.', $inventory->getLocation());
            }

            $em->remove($inventory);
            $em->flush();

            return $responseService->itemActionSuccess('You get ready to toss the Flowerbomb, but it explodes in your hands! Flowers go flying everywhere! (Mostly into your house.)', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        }
        else
        {
            $target = $userRepository->findOneRecentlyActive($user);

            if($target === null)
                return $responseService->itemActionSuccess('Hm... there\'s no one to toss it to! (I guess no one\'s been playing Poppy Seed Pets...)');

            $inventory
                ->setOwner($target)
                ->addComment($user->getName() . ' tossed this to ' . $target->getName() . '!')
                ->setLocation(LocationEnum::HOME)
                ->setModifiedOn()
                ->setSellPrice(null)
            ;

            $em->flush();

            return $responseService->itemActionSuccess('You toss the Flowerbomb to <a href="/poppyopedia/resident/' . $target->getId() . '">' . $target->getName() . '</a>!', [ 'itemDeleted' => true ]);
        }
    }
}
