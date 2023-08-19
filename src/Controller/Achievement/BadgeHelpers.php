<?php
namespace App\Controller\Achievement;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Entity\UserStats;
use App\Entity\UserUnlockedFeature;
use App\Enum\BadgeEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Model\TraderOfferCostOrYield;
use Doctrine\ORM\EntityManagerInterface;

final class BadgeHelpers
{
    public static function getUnlockedFeatures(User $user, array $featureNames): int
    {
        return array_reduce(
            $user->getUnlockedFeatures()->getValues(),
            fn(int $carry, UserUnlockedFeature $feature) => $carry + (int)in_array($feature->getFeature(), $featureNames),
            0
        );
    }

    public static function getUnlockedAuras(User $user): int
    {
        return $user->getUnlockedAuras()->count();
    }

    public static function getCompletedBadges(User $user, array $badgeNames): int
    {
        return array_reduce(
            $user->getBadges()->getValues(),
            fn(int $carry, UserBadge $badge) => $carry + (int)in_array($badge->getBadge(), $badgeNames),
            0
        );
    }

    private static $perRequestStatTotalCache = [];

    public static function getStatTotal(EntityManagerInterface $em, User $user, array $statNames): int
    {
        $key = $user->getId() . ':' . implode(',', $statNames);

        if(!array_key_exists($key, self::$perRequestStatTotalCache))
        {
            self::$perRequestStatTotalCache[$key] = (int)($em->createQueryBuilder()
                ->select('SUM(s.value)')
                ->from(UserStats::class, 's')
                ->andWhere('s.user = :user')
                ->andWhere('s.stat IN (:stats)')
                ->setParameter('user', $user)
                ->setParameter('stats', $statNames)
                ->getQuery()
                ->getSingleScalarResult());
        }

        return self::$perRequestStatTotalCache[$key];
    }

    public static function getBadgeProgress(string $badge, User $user, EntityManagerInterface $em): array
    {
        switch($badge)
        {
            case BadgeEnum::RECYCLED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_RECYCLED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Sand Dollar' ]), 1);
                break;

            case BadgeEnum::RECYCLED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_RECYCLED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Minor Scroll of Riches' ]), 1);
                break;

            case BadgeEnum::RECYCLED_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_RECYCLED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Major Scroll of Riches' ]), 1);
                break;

            case BadgeEnum::RECYCLED_10000:
                $progress = [ 'target' => 10000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_RECYCLED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Ruby Chest' ]), 1);
                break;

            case BadgeEnum::BAABBLES_OPENED_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Opened a Black Baabble', 'Opened a White Baabble', 'Opened a Gold Baabble', 'Opened a Shiny Baabble' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Key Ring' ]), 1);
                break;

            case BadgeEnum::BAABBLES_OPENED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Opened a Black Baabble', 'Opened a White Baabble', 'Opened a Gold Baabble', 'Opened a Shiny Baabble' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Carrot Key' ]), 1);
                break;

            case BadgeEnum::BAABBLES_OPENED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ 'Opened a Black Baabble', 'Opened a White Baabble', 'Opened a Gold Baabble', 'Opened a Shiny Baabble' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Winged Key' ]), 1);
                break;

            case BadgeEnum::BAABBLES_OPENED_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ 'Opened a Black Baabble', 'Opened a White Baabble', 'Opened a Gold Baabble', 'Opened a Shiny Baabble' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Skill Scroll: Crafts' ]), 1);
                break;

            case BadgeEnum::MONEYS_SPENT_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOTAL_MONEYS_SPENT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Canned Food' ]), 1);
                break;

            case BadgeEnum::MONEYS_SPENT_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOTAL_MONEYS_SPENT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Sandbox' ]), 1);
                break;

            case BadgeEnum::MONEYS_SPENT_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOTAL_MONEYS_SPENT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Monster Box' ]), 2);
                break;

            case BadgeEnum::MONEYS_SPENT_10000:
                $progress = [ 'target' => 10000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOTAL_MONEYS_SPENT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Box Box' ]), 2);
                break;

            case BadgeEnum::MONEYS_SPENT_100000:
                $progress = [ 'target' => 100000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOTAL_MONEYS_SPENT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Hat Box' ]), 2);
                break;

            case BadgeEnum::PETTED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::PETTED_A_PET ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Fortune Cookie' ]), 1);
                break;

            case BadgeEnum::PETTED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::PETTED_A_PET ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Renaming Scroll' ]), 1);
                break;

            case BadgeEnum::PETTED_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::PETTED_A_PET ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Behatting Scroll' ]), 1);
                break;

            case BadgeEnum::PETTED_10000:
                $progress = [ 'target' => 10000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::PETTED_A_PET ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Forgetting Scroll' ]), 1);
                break;

            case BadgeEnum::MAX_PETS_4:
                $progress = [ 'target' => 4, 'current' => $user->getMaxPets() ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Goodberries' ]), 4);
                break;

            case BadgeEnum::COMPLETE_THE_HEARTSTONE_DIMENSION:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Pet Completed the Heartstone Dimension' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Juice Box' ]), 1);
                break;

            case BadgeEnum::HATTIER_STYLES_10:
                $progress = [ 'target' => 10, 'current' => self::getUnlockedAuras($user) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Gravy' ]), 1);
                break;

            case BadgeEnum::HATTIER_STYLES_20:
                $progress = [ 'target' => 20, 'current' => self::getUnlockedAuras($user) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Googly Eyes' ]), 1);
                break;

            case BadgeEnum::HATTIER_STYLES_30:
                $progress = [ 'target' => 30, 'current' => self::getUnlockedAuras($user) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Jelling Polyp' ]), 1);
                break;

            case BadgeEnum::TROPHIES_EARNED_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Silver Trophies Earned', 'Gold Trophies Earned' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Little Strongbox' ]), 1);
                break;

            case BadgeEnum::TROPHIES_EARNED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Silver Trophies Earned', 'Gold Trophies Earned' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Gold Chest' ]), 1);
                break;

            case BadgeEnum::TROPHIES_EARNED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ 'Silver Trophies Earned', 'Gold Trophies Earned' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Ruby Chest' ]), 1);
                break;

            case BadgeEnum::OPENED_CEREAL_BOX:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Opened a Cereal Box' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Marshmallows' ]), 5);
                break;

            case BadgeEnum::OPENED_CAN_OF_FOOD_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Cans of Food Opened' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Scroll of Resources' ]), 2);
                break;

            case BadgeEnum::OPENED_CAN_OF_FOOD_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ 'Cans of Food Opened' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Scroll of Resources' ]), 10);
                break;

            case BadgeEnum::OPENED_PAPER_BAG_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Opened a Paper Bag' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Scroll of Resources' ]), 2);
                break;

            case BadgeEnum::OPENED_PAPER_BAG_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ 'Opened a Paper Bag' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Scroll of Resources' ]), 10);
                break;

            case BadgeEnum::OPENED_CAN_OF_FOOD_PAPER_BAG_100:
                $progress = [ 'target' => 3, 'current' => self::getCompletedBadges($user, [ BadgeEnum::OPENED_CEREAL_BOX, BadgeEnum::OPENED_CAN_OF_FOOD_100, BadgeEnum::OPENED_PAPER_BAG_100 ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => '5-leaf Clover' ]), 1);
                break;

            case BadgeEnum::HORRIBLE_EGGPLANT_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ROTTEN_EGGPLANTS ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Bag of Fertilizer' ]), 1);
                break;

            case BadgeEnum::HORRIBLE_EGGPLANT_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ROTTEN_EGGPLANTS ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Large Bag of Fertilizer' ]), 10);
                break;

            case BadgeEnum::HOT_POTATO_TOSSED_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOSSED_A_HOT_POTATO ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Potato' ]), 1);
                break;

            case BadgeEnum::HOT_POTATO_TOSSED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOSSED_A_HOT_POTATO ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Potato' ]), 10);
                break;

            case BadgeEnum::HOT_POTATO_TOSSED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOSSED_A_HOT_POTATO ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Potato' ]), 100);
                break;

            case BadgeEnum::FERTILIZED_PLANT_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::FERTILIZED_PLANT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Moon Dust' ]), 1);
                break;

            case BadgeEnum::FERTILIZED_PLANT_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::FERTILIZED_PLANT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Alien Tissue' ]), 2);
                break;

            case BadgeEnum::FERTILIZED_PLANT_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::FERTILIZED_PLANT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Wormhole' ]), 2);
                break;

            case BadgeEnum::HARVESTED_PLANT_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::HARVESTED_PLANT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Moon Pearl' ]), 1);
                break;

            case BadgeEnum::HARVESTED_PLANT_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::HARVESTED_PLANT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'New Moon' ]), 1);
                break;

            case BadgeEnum::HARVESTED_PLANT_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::HARVESTED_PLANT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Tile: The Cosmologer' ]), 1);
                break;

            case BadgeEnum::COMPOSTED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_COMPOSTED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Ants on a Log' ]), 1);
                break;

            case BadgeEnum::COMPOSTED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_COMPOSTED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Stinkier Bug' ]), 1);
                break;

            case BadgeEnum::COMPOSTED_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_COMPOSTED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Ant Queen\'s Favor' ]), 1);
                break;

            case BadgeEnum::FERTILIZED_HARVESTED_COMPOSTED_1000:
                $progress = [ 'target' => 3, 'current' => self::getCompletedBadges($user, [ BadgeEnum::FERTILIZED_PLANT_1000, BadgeEnum::HARVESTED_PLANT_1000, BadgeEnum::COMPOSTED_1000 ] )];
                $reward = TraderOfferCostOrYield::createRecyclingPoints(1000);
                break;

            case BadgeEnum::TREASURES_GIVEN_TO_DRAGON_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TREASURES_GIVEN_TO_DRAGON_HOARD ]) ];
                $reward = TraderOfferCostOrYield::createMoney(10);
                break;

            case BadgeEnum::TREASURES_GIVEN_TO_DRAGON_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TREASURES_GIVEN_TO_DRAGON_HOARD ]) ];
                $reward = TraderOfferCostOrYield::createMoney(100);
                break;

            case BadgeEnum::TREASURES_GIVEN_TO_DRAGON_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TREASURES_GIVEN_TO_DRAGON_HOARD ]) ];
                $reward = TraderOfferCostOrYield::createMoney(1000);
                break;

            case BadgeEnum::DRAGON_VASE_DIPPING_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::TOOLS_DIPPED_IN_A_DRAGON_VASE ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Eat Your Fruits and Veggies' ]), 1);
                break;

            case BadgeEnum::HOT_POT_DIPPING_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::FOODS_DIPPED_IN_A_HOT_POT ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate Sword' ]), 1);
                break;

            case BadgeEnum::COOKED_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::COOKED_SOMETHING ]) ];
                $reward = TraderOfferCostOrYield::createRecyclingPoints(10);
                break;

            case BadgeEnum::COOKED_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::COOKED_SOMETHING ]) ];
                $reward = TraderOfferCostOrYield::createRecyclingPoints(50);
                break;

            case BadgeEnum::COOKED_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::COOKED_SOMETHING ]) ];
                $reward = TraderOfferCostOrYield::createRecyclingPoints(250);
                break;

            case BadgeEnum::COOKED_10000:
                $progress = [ 'target' => 10000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::COOKED_SOMETHING ]) ];
                $reward = TraderOfferCostOrYield::createRecyclingPoints(1000);
                break;

            case BadgeEnum::TEACH_COOKING_BUDDY_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Baker\'s Box' ]), 1);
                break;

            case BadgeEnum::TEACH_COOKING_BUDDY_200:
                $progress = [ 'target' => 200, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Fruits & Veggies Box' ]), 1);
                break;

            case BadgeEnum::TEACH_COOKING_BUDDY_300:
                $progress = [ 'target' => 300, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Farmer\'s Scroll' ]), 1);
                break;

            case BadgeEnum::TEACH_COOKING_BUDDY_400:
                $progress = [ 'target' => 400, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Nature Box' ]), 1);
                break;

            case BadgeEnum::TEACH_COOKING_BUDDY_500:
                $progress = [ 'target' => 500, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Skill Scroll: Nature' ]), 1);
                break;

            case BadgeEnum::TEACH_COOKING_BUDDY_600:
                $progress = [ 'target' => 600, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Tile: Wild Herbs & Vegetables' ]), 1);
                break;


            case BadgeEnum::DEFEATED_SUMMONED_MONSTER_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Won Against Something... Unfriendly' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Gold Chest' ]), 1);
                break;

            case BadgeEnum::DEFEATED_SUMMONED_MONSTER_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Won Against Something... Unfriendly' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Ruby Chest' ]), 1);
                break;

            case BadgeEnum::DEFEATED_SUMMONED_MONSTER_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ 'Won Against Something... Unfriendly' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Skill Scroll: Brawl' ]), 1);
                break;

            case BadgeEnum::ASCENDED_TOWER_OF_TRIALS_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Opened a Tower Chest' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Scroll of Tell Samarzhoustian Delights' ]), 1);
                break;

            case BadgeEnum::ASCENDED_TOWER_OF_TRIALS_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Opened a Tower Chest' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Scroll of Dice' ]), 2);
                break;

            case BadgeEnum::ASCENDED_TOWER_OF_TRIALS_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ 'Opened a Tower Chest' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Skill Scroll: Brawl' ]), 1);
                break;

            case BadgeEnum::HOLLOW_EARTH_TRAVEL_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::HOLLOW_EARTH_SPACES_MOVED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Megalium' ]), 2);
                break;

            case BadgeEnum::HOLLOW_EARTH_TRAVEL_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::HOLLOW_EARTH_SPACES_MOVED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Piece of Cetgueli\'s Map' ]), 1);
                break;

            case BadgeEnum::HOLLOW_EARTH_TRAVEL_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::HOLLOW_EARTH_SPACES_MOVED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Monster-summoning Scroll' ]), 2);
                break;

            case BadgeEnum::MISREAD_SCROLL:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Misread a Scroll' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Pectin' ]), 1);
                break;

            case BadgeEnum::READ_SCROLL_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::READ_A_SCROLL ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Ponzu' ]), 2);
                break;

            case BadgeEnum::READ_SCROLL_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::READ_A_SCROLL ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Toad Jelly' ]), 2);
                break;

            case BadgeEnum::READ_SCROLL_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::READ_A_SCROLL ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Spice Rack' ]), 5);
                break;

            case BadgeEnum::WHISPER_STONE:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Listened to a Whisper Stone' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Magic Smoke' ]), 2);
                break;

            case BadgeEnum::OPENED_HAT_BOX_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Opened a Hat Box' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Coconut Half' ]), 1);
                break;

            case BadgeEnum::OPENED_HAT_BOX_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Opened a Hat Box' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Behatting Scroll' ]), 1);
                break;

            case BadgeEnum::OPENED_BOX_BOX_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Opened a Box Box' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Glowing Six-sided Die' ]), 1);
                break;

            case BadgeEnum::OPENED_BOX_BOX_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ 'Opened a Box Box' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Glowing Ten-sided Die' ]), 10);
                break;

            case BadgeEnum::BOX_BOX_BOX_BOX:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ 'Found a Box Box Inside a Box Box' ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Box Box' ]), 1);
                break;

            case BadgeEnum::PLAZA_BOX_1:
                $progress = [ 'target' => 1, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::PLAZA_BOXES_RECEIVED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Sand Dollar' ]), 1);
                break;

            case BadgeEnum::PLAZA_BOX_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::PLAZA_BOXES_RECEIVED ]) ];
                $reward = TraderOfferCostOrYield::createMoney(100);
                break;

            case BadgeEnum::PLAZA_BOX_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::PLAZA_BOXES_RECEIVED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Very Strongbox' ]), 2);
                break;

            // Fireplace

            case BadgeEnum::LONGEST_FIRE_1_HOUR:
                $progress = [ 'target' => 60, 'current' => $user->getFireplace() ? $user->getFireplace()->getLongestStreak() : 0 ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Blackberry Wine' ]), 2);
                break;

            case BadgeEnum::LONGEST_FIRE_1_DAY:
                $progress = [ 'target' => 1440, 'current' => $user->getFireplace() ? $user->getFireplace()->getLongestStreak() : 0 ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Crooked Stick' ]), 2);
                break;

            case BadgeEnum::LONGEST_FIRE_1_WEEK:
                $progress = [ 'target' => 10080, 'current' => $user->getFireplace() ? $user->getFireplace()->getLongestStreak() : 0 ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Firestone' ]), 2);
                break;

            case BadgeEnum::FIREPLACE_FUEL_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_THROWN_INTO_THE_FIREPLACE ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Kilju' ]), 2);
                break;

            case BadgeEnum::FIREPLACE_FUEL_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_THROWN_INTO_THE_FIREPLACE ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Charcoal' ]), 2);
                break;

            case BadgeEnum::FIREPLACE_FUEL_1000:
                $progress = [ 'target' => 1000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_THROWN_INTO_THE_FIREPLACE ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Witch\'s Broom' ]), 2);
                break;

            case BadgeEnum::FIREPLACE_FUEL_10000:
                $progress = [ 'target' => 10000, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ITEMS_THROWN_INTO_THE_FIREPLACE ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Ceremony of Fire' ]), 2);
                break;

            // Bugs

            case BadgeEnum::FEED_THE_CENTIPEDES_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::EVOLVED_A_CENTIPEDE ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Wings' ]), 2);
                break;

            case BadgeEnum::FEED_THE_ANTS_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::FED_A_LINE_OF_ANTS ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Sugar' ]), 10);
                break;

            case BadgeEnum::FEED_THE_BEES_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::FED_THE_BEEHIVE ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Sugar' ]), 10);
                break;

            case BadgeEnum::PLAYING_BOTH_SIDES:
                $progress = [ 'target' => 2, 'current' => self::getCompletedBadges($user, [ BadgeEnum::FEED_THE_ANTS_10, BadgeEnum::FEED_THE_BEES_10 ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Dicerca' ]), 1);
                break;

            // Meta

            case BadgeEnum::ACCOUNT_AGE_365:
                $progress = [ 'target' => 365, 'current' => (new \DateTimeImmutable())->diff($user->getRegisteredOn())->days ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Candle' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_10:
                $progress = [ 'target' => 10, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate Bar' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_20:
                $progress = [ 'target' => 20, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate Meringue' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_30:
                $progress = [ 'target' => 30, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate Toffee Matzah' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_40:
                $progress = [ 'target' => 40, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Mini Chocolate Chip Cookies' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_50:
                $progress = [ 'target' => 50, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate Lava Cake' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_60:
                $progress = [ 'target' => 60, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate Cake Pops' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_70:
                $progress = [ 'target' => 70, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate-covered Naner' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_80:
                $progress = [ 'target' => 80, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Slice of Chocolate Cream Pie' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_90:
                $progress = [ 'target' => 90, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate-frosted Donut' ]), 1);
                break;

            case BadgeEnum::ACHIEVEMENTS_100:
                $progress = [ 'target' => 100, 'current' => self::getStatTotal($em, $user, [ UserStatEnum::ACHIEVEMENTS_CLAIMED ]) ];
                $reward = TraderOfferCostOrYield::createItem($em->getRepository(Item::class)->findOneBy([ 'name' => 'Chocolate Chest' ]), 1);
                break;

            // Chocolate-covered Honeycomb
            // Chocolate Ice Cream Sammy

            default:
                throw new \Exception('Oops! Badge not implemented! Ben was a bad programmer!');
        }

        return [
            'badge' => $badge,
            'progress' => $progress,
            'done' => $progress['current'] >= $progress['target'],
            'reward' => $reward
        ];
    }

}