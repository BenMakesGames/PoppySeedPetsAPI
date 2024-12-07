<?php
namespace App\Functions;

use App\Entity\User;
use App\Entity\UserMenuOrder;
use App\Enum\UnlockableFeatureEnum;
use App\Model\UserMenuItem;
use Doctrine\ORM\EntityManagerInterface;

final class UserMenuFunctions
{
    private const DEFAULT_ORDER = [
        'home', 'cookingBuddy', 'basement', 'greenhouse', 'beehive', 'dragonDen', 'hollowEarth', 'starKindred',
        'fireplace', 'park', 'plaza', 'museum', 'zoologist', 'market', 'grocer', 'petShelter',
        'bookstore', 'trader', 'hattier', 'fieldGuide', 'mailbox', 'painter', 'florist',
        'journal', 'achievements'
    ];

    public static function updateUserMenuSortOrder(EntityManagerInterface $em, User $user, array $order)
    {
        $order = array_filter($order, fn($o) => in_array($o, self::DEFAULT_ORDER));

        foreach(self::DEFAULT_ORDER as $o)
        {
            if(!in_array($o, $order))
                $order[] = $o;
        }

        $userSortOrderEntity = $em->getRepository(UserMenuOrder::class)->findOneBy([ 'user' => $user ]);

        if(!$userSortOrderEntity)
        {
            $userSortOrderEntity = (new UserMenuOrder())
                ->setUser($user)
            ;

            $em->persist($userSortOrderEntity);
        }

        $userSortOrderEntity->setMenuOrder($order);
    }

    private static function maybeAddMenuItem(array &$menuItems, string $name, User $user, array $userSortOrders, ?string $feature): bool
    {
        if(!$feature)
        {
            $menuItems[] = new UserMenuItem($name, array_search($name, $userSortOrders), null);

            return true;
        }

        $date = $user->getUnlockedFeatureDate($feature);

        if($date == null)
            return false;

        $menuItems[] = new UserMenuItem($name, array_search($name, $userSortOrders), $date);

        return true;
    }

    public static function getUserMenuItems(EntityManagerInterface $em, User $user): array
    {
        $userSortOrderEntity = $em->getRepository(UserMenuOrder::class)->findOneBy([ 'user' => $user ]);

        $userSortOrder = $userSortOrderEntity
            ? $userSortOrderEntity->getMenuOrder()
            : self::DEFAULT_ORDER
        ;

        $menuItems = [];
        $locked =
            (self::maybeAddMenuItem($menuItems, 'home', $user, $userSortOrder, null) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'cookingBuddy', $user, $userSortOrder, UnlockableFeatureEnum::CookingBuddy) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'basement', $user, $userSortOrder, UnlockableFeatureEnum::Basement) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'greenhouse', $user, $userSortOrder, UnlockableFeatureEnum::Greenhouse) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'beehive', $user, $userSortOrder, UnlockableFeatureEnum::Beehive) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'fireplace', $user, $userSortOrder, UnlockableFeatureEnum::Fireplace) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'dragonDen', $user, $userSortOrder, UnlockableFeatureEnum::DragonDen) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'hollowEarth', $user, $userSortOrder, UnlockableFeatureEnum::HollowEarth) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'starKindred', $user, $userSortOrder, UnlockableFeatureEnum::StarKindred) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'park', $user, $userSortOrder, UnlockableFeatureEnum::Park) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'plaza', $user, $userSortOrder, null) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'museum', $user, $userSortOrder, UnlockableFeatureEnum::Museum) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'zoologist', $user, $userSortOrder, UnlockableFeatureEnum::Zoologist) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'market', $user, $userSortOrder, UnlockableFeatureEnum::Market) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'grocer', $user, $userSortOrder, null) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'petShelter', $user, $userSortOrder, null) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'bookstore', $user, $userSortOrder, UnlockableFeatureEnum::Bookstore) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'trader', $user, $userSortOrder, UnlockableFeatureEnum::Trader) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'hattier', $user, $userSortOrder, UnlockableFeatureEnum::Hattier) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'fieldGuide', $user, $userSortOrder, UnlockableFeatureEnum::FieldGuide) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'mailbox', $user, $userSortOrder, UnlockableFeatureEnum::Mailbox) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'painter', $user, $userSortOrder, null) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'florist', $user, $userSortOrder, UnlockableFeatureEnum::Florist) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'journal', $user, $userSortOrder, null) ? 0 : 1) +
            (self::maybeAddMenuItem($menuItems, 'achievements', $user, $userSortOrder, null) ? 0 : 1);

        return [
            'items' => $menuItems,
            'numberLocked' => $locked
        ];
    }
}
