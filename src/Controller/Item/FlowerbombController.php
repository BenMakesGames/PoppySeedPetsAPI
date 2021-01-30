<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserQuestRepository;
use App\Repository\UserRepository;
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
        UserRepository $userRepository, InventoryService $inventoryService, UserQuestRepository $userQuestRepository
    )
    {
        $this->validateInventory($inventory, 'flowerbomb/#/toss');

        $user = $this->getUser();

        $lastFlowerBombWasNarcissistic = $userQuestRepository->findOrCreate($user, 'Last Flowerbomb was Narcissus', true);

        $numberOfTosses = 0;

        foreach($inventory->getComments() as $comment)
        {
            if(strpos($comment, ' tossed this to ') !== false)
                $numberOfTosses++;
        }

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
