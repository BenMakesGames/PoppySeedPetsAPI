<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserMenuOrder;
use App\Enum\UnlockableFeatureEnum;
use App\Model\UserMenuItem;
use App\Repository\UserMenuOrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserMenuService
{
    private $userMenuOrderRepository;
    private $em;

    private const DEFAULT_ORDER = [
        'home', 'basement', 'greenhouse', 'beehive', 'dragonDen', 'hollowEarth', 'starKindred',
        'fireplace', 'park', 'plaza', 'museum', 'market', 'grocer', 'petShelter',
        'bookstore', 'trader', 'hattier', 'fieldGuide', 'mailbox', 'painter', 'florist',
        'journal'
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

    private static function maybeAddMenuItem(array &$menuItems, string $name, User $user, array $userSortOrders, ?string $feature)
    {
        if(!$feature)
        {
            $menuItems[] = new UserMenuItem($name, array_search($name, $userSortOrders), null);

            return;
        }

        $date = $user->getUnlockedFeatureDate($feature);

        if($date == null)
            return;

        $menuItems[] = new UserMenuItem($name, array_search($name, $userSortOrders), $date);
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

        $this->maybeAddMenuItem($menuItems, 'home', $user, $userSortOrder, null);
        $this->maybeAddMenuItem($menuItems, 'basement', $user, $userSortOrder, UnlockableFeatureEnum::Basement);
        $this->maybeAddMenuItem($menuItems, 'greenhouse', $user, $userSortOrder, UnlockableFeatureEnum::Greenhouse);
        $this->maybeAddMenuItem($menuItems, 'beehive', $user, $userSortOrder, UnlockableFeatureEnum::Beehive);
        $this->maybeAddMenuItem($menuItems, 'dragonDen', $user, $userSortOrder, UnlockableFeatureEnum::DragonDen);
        $this->maybeAddMenuItem($menuItems, 'hollowEarth', $user, $userSortOrder, UnlockableFeatureEnum::HollowEarth);
        $this->maybeAddMenuItem($menuItems, 'starKindred', $user, $userSortOrder, null);
        $this->maybeAddMenuItem($menuItems, 'fireplace', $user, $userSortOrder, UnlockableFeatureEnum::Fireplace);
        $this->maybeAddMenuItem($menuItems, 'park', $user, $userSortOrder, UnlockableFeatureEnum::Park);
        $this->maybeAddMenuItem($menuItems, 'plaza', $user, $userSortOrder, null);
        $this->maybeAddMenuItem($menuItems, 'museum', $user, $userSortOrder, UnlockableFeatureEnum::Museum);
        $this->maybeAddMenuItem($menuItems, 'market', $user, $userSortOrder, UnlockableFeatureEnum::Market);
        $this->maybeAddMenuItem($menuItems, 'grocer', $user, $userSortOrder, UnlockableFeatureEnum::Market); // also unlocked with Market!
        $this->maybeAddMenuItem($menuItems, 'petShelter', $user, $userSortOrder, null);
        $this->maybeAddMenuItem($menuItems, 'bookstore', $user, $userSortOrder, UnlockableFeatureEnum::Bookstore);
        $this->maybeAddMenuItem($menuItems, 'trader', $user, $userSortOrder, UnlockableFeatureEnum::Trader);
        $this->maybeAddMenuItem($menuItems, 'hattier', $user, $userSortOrder, UnlockableFeatureEnum::Hattier);
        $this->maybeAddMenuItem($menuItems, 'fieldGuide', $user, $userSortOrder, UnlockableFeatureEnum::FieldGuide);
        $this->maybeAddMenuItem($menuItems, 'mailbox', $user, $userSortOrder, UnlockableFeatureEnum::Mailbox);
        $this->maybeAddMenuItem($menuItems, 'painter', $user, $userSortOrder, null);
        $this->maybeAddMenuItem($menuItems, 'florist', $user, $userSortOrder, UnlockableFeatureEnum::Florist);
        $this->maybeAddMenuItem($menuItems, 'journal', $user, $userSortOrder, null);

        return $menuItems;
    }
}