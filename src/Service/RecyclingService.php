<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\User;
use App\Entity\UserActivityLog;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\UserActivityLogTagRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RecyclingService
{
    private UserRepository $userRepository;
    private CalendarService $calendarService;
    private EntityManagerInterface $em;
    private UserStatsRepository $userStatsRepository;
    private IRandom $squirrel3;
    private ResponseService $responseService;
    private UserActivityLogTagRepository $userActivityLogTagRepository;

    public function __construct(
        UserRepository $userRepository, CalendarService $calendarService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, Squirrel3 $squirrel3, ResponseService $responseService,
        UserActivityLogTagRepository $userActivityLogTagRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->calendarService = $calendarService;
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
        $this->squirrel3 = $squirrel3;
        $this->responseService = $responseService;
        $this->userActivityLogTagRepository = $userActivityLogTagRepository;
    }

    public static function giveRecyclingPoints(User $user, int $quantity)
    {
        if($quantity == 0)
            return;

        $user->increaseRecyclePoints($quantity);

        if($user->getUnlockedRecycling() === null)
            $user->setUnlockedRecycling();
    }

    private static function recycledItemShouldGoToGivingTree(IRandom $rng, bool $givingTreeHoliday, Inventory $i): bool
    {
        if($i->getLockedToOwner())
            return false;

        $item = $i->getItem();

        if($rng->rngNextInt(1, 8 + $item->getRecycleValue() * 2) === 1)
            return true;

        if($givingTreeHoliday && $item->getFood() && $item->getFood()->getIsCandy())
            return true;

        return false;
    }

    /**
     * @param Inventory[] $inventory
     * @return int[] IDs of items NOT recycled
     */
    public function recycleInventory(User $user, array $inventory): array
    {
        $givingTree = $this->userRepository->findOneByEmail('giving-tree@poppyseedpets.com');

        if(!$givingTree)
            throw new HttpException(500, 'The "Giving Tree" NPC does not exist in the database!');

        $givingTreeHoliday = $this->calendarService->isValentinesOrAdjacent() || $this->calendarService->isWhiteDay();
        $questItems = [];
        $idsNotRecycled = [];

        $totalItemsRecycled = 0;
        $totalRecyclingPointsEarned = 0;

        foreach($inventory as $i)
        {
            if($i->getOwner()->getId() !== $user->getId())
                throw new \Exception('Cannot recycle items that do not belong to you!');

            if($i->getItem()->getCannotBeThrownOut())
            {
                $questItems[] = $i->getItem()->getName();
                $idsNotRecycled[] = $i->getId();

                continue;
            }

            if($i->getItem()->hasUseAction('bug/#/putOutside'))
            {
                $this->userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE);
                $this->em->remove($i);
                continue;
            }

            if(self::recycledItemShouldGoToGivingTree($this->squirrel3, $givingTreeHoliday, $i))
            {
                $i
                    ->setOwner($givingTree)
                    ->setLocation(LocationEnum::HOME)
                    ->setSellPrice(null)
                    ->addComment($user->getName() . ' recycled this item, and it found its way to The Giving Tree!')
                ;

                if($i->getHolder()) $i->getHolder()->setTool(null);
                if($i->getWearer()) $i->getWearer()->setHat(null);
            }
            else
                $this->em->remove($i);

            $totalItemsRecycled++;
            $totalRecyclingPointsEarned += $i->getItem()->getRecycleValue();
        }

        if($totalRecyclingPointsEarned > 0 || $totalItemsRecycled > 0)
        {
            self::giveRecyclingPoints($user, $totalRecyclingPointsEarned);

            $this->userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_RECYCLED, $totalItemsRecycled);

            $playerLog = (new UserActivityLog())
                ->setUser($user)
                ->addTags($this->userActivityLogTagRepository->findByNames([ 'Recycling' ]))
                ->setEntry('You recycled ' . $totalItemsRecycled . ' item' . ($totalItemsRecycled == 1 ? '' : 's') . ' for ' . $totalRecyclingPointsEarned . ' recycling point' . ($totalRecyclingPointsEarned == 1 ? '' : 's') . '.')
            ;
            $this->em->persist($playerLog);
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
