<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
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

    public function __construct(
        UserRepository $userRepository, CalendarService $calendarService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->calendarService = $calendarService;
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
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

        if(mt_rand(1, 10) === 1)
            return true;

        if($givingTreeHoliday && $i->getItem()->getFood() && $i->getItem()->getFood()->getIsCandy())
            return true;

        return false;
    }

    /**
     * @param Inventory[] $inventory
     */
    public function recycleInventory(array $inventory)
    {
        $givingTree = $this->userRepository->findOneByEmail('giving-tree@poppyseedpets.com');

        if(!$givingTree)
            throw new HttpException(500, 'The "Giving Tree" NPC does not exist in the database!');

        $givingTreeHoliday = $this->calendarService->isValentines() || $this->calendarService->isWhiteDay();

        foreach($inventory as $i)
        {
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
    }
}
