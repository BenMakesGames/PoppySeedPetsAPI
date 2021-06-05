<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RecyclingService
{
    private $userRepository;
    private $calendarService;
    private $em;
    private $userStatsRepository;
    private IRandom $squirrel3;
    private ResponseService $responseService;

    public function __construct(
        UserRepository $userRepository, CalendarService $calendarService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, Squirrel3 $squirrel3, ResponseService $responseService
    )
    {
        $this->userRepository = $userRepository;
        $this->calendarService = $calendarService;
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
        $this->squirrel3 = $squirrel3;
        $this->responseService = $responseService;
    }

    public function giveRecyclingPoints(User $user, int $quantity)
    {
        if($quantity == 0)
            return;

        $user->increaseRecyclePoints($quantity);

        if($user->getUnlockedRecycling() === null)
            $user->setUnlockedRecycling();
    }

    private function recycledItemShouldGoToGivingTree(bool $givingTreeHoliday, Inventory $i)
    {
        if($i->getLockedToOwner())
            return false;

        $item = $i->getItem();

        if($this->squirrel3->rngNextInt(1, 8 + $item->getRecycleValue() * 2) === 1)
            return true;

        if($givingTreeHoliday && $item->getFood() && $item->getFood()->getIsCandy())
            return true;

        return false;
    }

    /**
     * @param Inventory[] $inventory
     * @return int[] IDs of items NOT recycled
     */
    public function recycleInventory(array $inventory): array
    {
        $givingTree = $this->userRepository->findOneByEmail('giving-tree@poppyseedpets.com');

        if(!$givingTree)
            throw new HttpException(500, 'The "Giving Tree" NPC does not exist in the database!');

        $givingTreeHoliday = $this->calendarService->isValentines() || $this->calendarService->isWhiteDay();
        $questItems = [];
        $idsNotRecycled = [];

        foreach($inventory as $i)
        {
            if($i->getItem()->getCannotBeThrownOut())
            {
                $questItems[] = $i->getItem()->getName();
                $idsNotRecycled[] = $i->getId();

                continue;
            }

            $originalOwner = $i->getOwner();

            if($i->getItem()->hasUseAction('bug/#/putOutside'))
            {
                $this->userStatsRepository->incrementStat($originalOwner, UserStatEnum::BUGS_PUT_OUTSIDE);
                $this->em->remove($i);
            }
            else if($this->recycledItemShouldGoToGivingTree($givingTreeHoliday, $i))
            {
                $i
                    ->setOwner($givingTree)
                    ->setLocation(LocationEnum::HOME)
                    ->setSellPrice(null)
                    ->addComment($originalOwner->getName() . ' recycled this item, and it found its way to The Giving Tree!')
                ;

                if($i->getHolder()) $i->getHolder()->setTool(null);
                if($i->getWearer()) $i->getWearer()->setHat(null);
            }
            else
                $this->em->remove($i);

            $this->giveRecyclingPoints($originalOwner, $i->getItem()->getRecycleValue());

            $this->userStatsRepository->incrementStat($originalOwner, UserStatEnum::ITEMS_RECYCLED);
        }

        if(count($questItems) > 0)
        {
            $questItems = array_unique($questItems);

            if(count($questItems) === 1)
                $this->responseService->addFlashMessage('The ' . $questItems[0] . ' look really important! You should hold on to that...');
            else
                $this->responseService->addFlashMessage('The ' . ArrayFunctions::list_nice($questItems) . ' look really important! You should hold on to those...');
        }

        return $idsNotRecycled;
    }
}
