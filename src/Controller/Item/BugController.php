<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Enum\UserStatEnum;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\StoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/bug")
 */
class BugController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/squish", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function squishBug(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, UserQuestRepository $userQuestRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/squish');

        $promised = $userQuestRepository->findOrCreate($user, 'Promised to Not Squish Bugs', 0);

        if($promised->getValue())
            return $responseService->itemActionSuccess('You\'ve promised not to squish any more bugs...');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_SQUISHED);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/putOutside", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function putBugOutside(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/putOutside');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE);
        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_THROWN_AWAY);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedBug(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, Request $request, InventoryRepository $inventoryRepository,
        InventoryService $inventoryService, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'feedBug');

        $item = $inventoryRepository->find($request->request->getInt('food'));

        if(!$item || $item->getOwner()->getId() !== $user->getId())
            throw new UnprocessableEntityHttpException('Must select an item to feed.');

        if(!$item->getItem()->getFood())
            throw new UnprocessableEntityHttpException('Bugs won\'t eat that item. (Bugs are bougie like that, I guess.)');

        switch($inventory->getItem()->getName())
        {
            case 'Centipede':
                $inventory
                    ->changeItem($itemRepository->findOneByName('Moth'))
                    ->addComment($user->getName() . ' fed this Centipede, allowing it to grow up into a beautiful... Moth.')
                    ->setModifiedOn()
                ;
                $message = "What? Centipede is evolving!\n\nCongratulations! Your Centipede evolved into... a Moth??";
                break;

            case 'Cockroach':
                $inventoryService->receiveItem('Cockroach', $user, $user, $user->getName() . ' fed a Cockroach; as a result, _this_ Cockroach showed up. (Is this a good thing?)', $inventory->getLocation());
                $message = 'Oh. You\'ve attracted another Cockroach!';
                break;

            case 'Line of Ants':
                if(mt_rand(1, 10) === 1)
                {
                    $inventoryService->receiveItem('Ant Queen', $user, $user, $user->getName() . ' fed a Line of Ants; as a result, this Queen Ant showed up! (Is this a good thing?)', $inventory->getLocation());
                    $message = 'Oh? You\'ve attracted an Ant Queen!';
                }
                else
                {
                    $inventoryService->receiveItem('Line of Ants', $user, $user, $user->getName() . ' fed a Line of Ants; as a result, _these_ ants showed up. (Is this a good thing?)', $inventory->getLocation());
                    $message = 'Oh. You\'ve attracted more ants!';
                }

                break;

            case 'Ant Queen':
                $inventoryService->receiveItem('Line of Ants', $user, $user, $user->getName() . ' fed an Ant Queen; as a result, _these_ ants showed up. (Is this a good thing?)', $inventory->getLocation());
                $message = 'Oh. You\'ve attracted more ants!';
                break;

            case 'Fruit Fly':
                $inventoryService->receiveItem('Fruit Fly', $user, $user, $user->getName() . ' fed a Fruit Fly; as a result, _this_ Fruit Fly showed up. (Is this a good thing?)', $inventory->getLocation());
                $message = 'Oh. You\'ve attracted another Fruit Fly!';
                break;

            default:
                throw new \Exception($inventory->getItem()->getName() . ' cannot be fed! This is totally a programmer\'s error, and should be fixed!');
        }

        $em->remove($item);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_FED);

        $em->flush();

        $responseService->addActivityLog((new PetActivityLog())->setEntry($message));

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/talkToQueen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @throws \Exception
     */
    public function talkToQueen(
        Inventory $inventory, StoryService $storyService, Request $request, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/squish');

        $response = $storyService->doStory($user, StoryEnum::STOLEN_PLANS, $request->request);

        return $responseService->success($response, SerializationGroupEnum::STORY);
    }
}