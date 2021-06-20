<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserMenuOrder;
use App\Model\UserMenuItem;
use App\Repository\UserMenuOrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserMenuService
{
    private $userMenuOrderRepository;
    private $em;

    private const DEFAULT_ORDER = [
        'home', 'basement', 'greenhouse', 'beehive', 'dragonDen', 'hollowEarth',
        'fireplace', 'park', 'plaza', 'museum', 'market', 'grocer', 'petShelter',
        'bookstore', 'trader', 'hattier', 'fieldGuide', 'mailbox', 'painter', 'florist'
    ];

    public function __construct(
        UserMenuOrderRepository $userMenuOrderRepository, EntityManagerInterface $em
    )
    {
        $this->userMenuOrderRepository = $userMenuOrderRepository;
        $this->em = $em;
    }

    public function updateUserMenuSortOrder(User $user, array $order)
    {
        $order = array_filter($order, fn($o) => in_array($o, self::DEFAULT_ORDER));

        foreach(self::DEFAULT_ORDER as $o)
        {
            if(!in_array($o, $order))
                $order[] = $o;
        }

        $userSortOrderEntity = $this->userMenuOrderRepository->findOneBy([ 'user' => $user ]);

        if(!$userSortOrderEntity)
        {
            $userSortOrderEntity = (new UserMenuOrder())
                ->setUser($user)
            ;

            $this->em->persist($userSortOrderEntity);
        }

        $userSortOrderEntity->setMenuOrder($order);
    }

    /**
     * @return UserMenuItem[]
     */
    public function getUserMenuItems(User $user): array
    {
        $userSortOrderEntity = $this->userMenuOrderRepository->findOneBy([ 'user' => $user ]);

        $userSortOrder = $userSortOrderEntity
            ? $userSortOrderEntity->getMenuOrder()
            : self::DEFAULT_ORDER
        ;

        $menuItems = [];

        $menuItems[] = new UserMenuItem('home', $userSortOrder, null);

        if($user->getUnlockedBasement())
            $menuItems[] = new UserMenuItem('basement', $userSortOrder, $user->getUnlockedBasement());

        if($user->getUnlockedGreenhouse())
            $menuItems[] = new UserMenuItem('greenhouse', $userSortOrder, $user->getUnlockedGreenhouse());

        if($user->getUnlockedBeehive())
            $menuItems[] = new UserMenuItem('beehive', $userSortOrder, $user->getUnlockedBeehive());

        if($user->getUnlockedDragonDen())
            $menuItems[] = new UserMenuItem('dragonDen', $userSortOrder, $user->getUnlockedDragonDen());

        if($user->getUnlockedHollowEarth())
            $menuItems[] = new UserMenuItem('hollowEarth', $userSortOrder, $user->getUnlockedHollowEarth());

        if($user->getUnlockedFireplace())
            $menuItems[] = new UserMenuItem('fireplace', $userSortOrder, $user->getUnlockedFireplace());

        if($user->getUnlockedPark())
            $menuItems[] = new UserMenuItem('park', $userSortOrder, $user->getUnlockedPark());

        $menuItems[] = new UserMenuItem('plaza', $userSortOrder, null);

        if($user->getUnlockedMuseum())
            $menuItems[] = new UserMenuItem('museum', $userSortOrder, $user->getUnlockedMuseum());

        if($user->getUnlockedMarket())
        {
            $menuItems[] = new UserMenuItem('market', $userSortOrder, $user->getUnlockedMarket());
            $menuItems[] = new UserMenuItem('grocer', $userSortOrder, $user->getUnlockedMarket());
        }

        $menuItems[] = new UserMenuItem('petShelter', $userSortOrder, null);

        if($user->getUnlockedBookstore())
            $menuItems[] = new UserMenuItem('bookstore', $userSortOrder, $user->getUnlockedBookstore());

        if($user->getUnlockedTrader())
            $menuItems[] = new UserMenuItem('trader', $userSortOrder, $user->getUnlockedTrader());

        if($user->getUnlockedHattier())
            $menuItems[] = new UserMenuItem('hattier', $userSortOrder, $user->getUnlockedHattier());

        if($user->getUnlockedFieldGuide())
            $menuItems[] = new UserMenuItem('fieldGuide', $userSortOrder, $user->getUnlockedFieldGuide());

        if($user->getUnlockedMailbox())
            $menuItems[] = new UserMenuItem('mailbox', $userSortOrder, $user->getUnlockedMailbox());

        $menuItems[] = new UserMenuItem('painter', $userSortOrder, null);

        if($user->getUnlockedFlorist())
            $menuItems[] = new UserMenuItem('florist', $userSortOrder, $user->getUnlockedFlorist());

        return $menuItems;
    }
}