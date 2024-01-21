<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\CalendarFunctions;
use Doctrine\ORM\EntityManagerInterface;

class RecyclingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly IRandom $rng,
        private readonly ResponseService $responseService,
        private readonly TransactionService $transactionService,
        private readonly Clock $clock
    )
    {
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
        $givingTree = $this->em->getRepository(User::class)->findOneBy([ 'email' => 'giving-tree@poppyseedpets.com' ]);

        if(!$givingTree)
            throw new \Exception('The "Giving Tree" NPC does not exist in the database!');

        $givingTreeHoliday = CalendarFunctions::isValentinesOrAdjacent($this->clock->now) || CalendarFunctions::isWhiteDay($this->clock->now);
        $questItems = [];
        $idsNotRecycled = [];

        $totalItemsRecycled = 0;
        $totalRecyclingPointsEarned = 0;

        foreach($inventory as $i)
        {
            if($i->getOwner()->getId() !== $user->getId())
                throw new PSPNotFoundException('Could not find one or more of the selected items... (Reload and try again?)');

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

            if(self::recycledItemShouldGoToGivingTree($this->rng, $givingTreeHoliday, $i))
            {
                $i
                    ->changeOwner($givingTree, $user->getName() . ' recycled this item, and it found its way to The Giving Tree!', $this->em)
                    ->setLocation(LocationEnum::HOME)
                ;
            }
            else
                $this->em->remove($i);

            $totalItemsRecycled++;
            $totalRecyclingPointsEarned += $i->getItem()->getRecycleValue();
        }

        if($totalRecyclingPointsEarned > 0 || $totalItemsRecycled > 0)
        {
            $this->userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_RECYCLED, $totalItemsRecycled);

            $this->transactionService->getRecyclingPoints(
                $user,
                $totalRecyclingPointsEarned,
                'You recycled ' . $totalItemsRecycled . ' item' . ($totalItemsRecycled == 1 ? '' : 's') . '.'
            );
        }

        if(count($questItems) > 0)
        {
            $questItems = array_unique($questItems);

            if(count($questItems) === 1)
                $this->responseService->addFlashMessage('The ' . $questItems[0] . ' looks really important! You should probably hold on to that...');
            else
                $this->responseService->addFlashMessage('The ' . ArrayFunctions::list_nice($questItems) . ' look really important! You should probably hold on to those...');
        }

        return $idsNotRecycled;
    }
}
