<?php
namespace App\Service;

use App\Functions\CalendarFunctions;
use App\Functions\DateFunctions;
use App\Functions\ItemRepository;
use App\Functions\RandomFunctions;
use Doctrine\ORM\EntityManagerInterface;

class GrocerService
{
    public const MAX_CAN_PURCHASE_PER_DAY = 20;

    public function __construct(
        private readonly CacheHelper $cacheHelper, private readonly EntityManagerInterface $em
    )
    {
    }

    // cost = fertilizer value + 2 + CEIL(chance_for_bonus_item / 50)
    private const HOT_BAR_ITEMS = [
        [ 'Basic Fish Taco', 11 ],
        [ 'Battered, Fried Fish', 8 ],
        [ 'Cake Pops', 10 ],
        [ 'Caramel-covered Red', 15 ],
        [ 'Cheese Quesadilla', 14 ],
        [ 'Chili Calamari', 8 ],
        [ 'Egg Custard', 7 ],
        [ 'Fisherman\'s Pie', 15 ],
        [ 'Fried Tomato', 9 ],
        [ 'Grilled Fish', 17 ],
        [ 'Hot Dog', 16 ],
        [ 'Largish Bowl of Smallish Pumpkin Soup', 8 ],
        [ 'Mango Sticky Rice', 10 ],
        [ 'Matzah Ball Soup', 11 ],
        [ 'Mighty Fried Bananas', 16 ],
        [ 'Minestrone', 14 ],
        [ 'Pan-fried Tofu', 10 ],
        [ 'Potato-mushroom Stuffed Onion', 10 ],
        [ 'Red Cobbler', 12 ],
        [ 'Shakshouka', 8 ],
        [ 'Slice of Blackberry Pie', 11 ],
        [ 'Spicy Deep-fried Toad Legs', 11 ],
        [ 'Spicy Fish Stew', 10 ],
        [ 'Sweet Roll', 13 ],
        [ 'Tapsilog', 18 ],
        [ 'Tentacle Fried Rice', 15 ],
        [ 'Yaki Onigiri', 11 ],
    ];

    private static function getItems(bool $isCornMoon): array
    {
        return [
            [ 'Baking Soda', 2 ],
            [ 'Coconut', 10 ],
            [ 'Creamy Milk', 4 ],
            [ 'Egg', 4 ],
            [ 'Fish', 10 ],
            [ 'Naner', 6 ],
            [ 'Onion', 4 ],
            [ 'Orange', 6 ],
            [ 'Red', 6 ],
            [ 'Rice', 4 ],
            [ 'Sugar', 4 ],
            [ 'Tofu', 8 ],
            [ 'Vinegar', 5 ],
            self::getWheatFlourOrCorn($isCornMoon),
        ];
    }

    private static function getWheatFlourOrCorn(bool $isCornMoon): array
    {
        if($isCornMoon)
            return [ 'Corn', 5 ];

        return [ 'Wheat Flour', 4 ];
    }

    public function getInventory()
    {
        $today = new \DateTimeImmutable();
        $day = (int)$today->format('Y') * 370 + (int)$today->format('z');

        return $this->cacheHelper->getOrCompute(
            'Grocery Store ' . $day,
            \DateInterval::createFromDateString('1 day'),
            fn() => $this->computeInventory($day)
        );
    }

    private function computeInventory(int $day): array
    {
        $inventory = [];
        $now = new \DateTimeImmutable();

        if(CalendarFunctions::isJelephantDay($now))
            $inventory[] = $this->createInventoryData([ 'Jelephant Aminal Crackers', 8 ], true);

        if(CalendarFunctions::isPiDay($now))
            $inventory[] = $this->createInventoryData([ 'Pi Pie', 46 ], true);

        if(CalendarFunctions::isAwaOdori($now))
            $inventory[] = $this->createInventoryData([ 'Odori 0.0%', 12 ], true);

        if(CalendarFunctions::isAHornOfPlentyDay($now))
            $inventory[] = $this->createInventoryData([ 'Horn of Plenty', 50 ], true);

        $hotBarIndex = RandomFunctions::squirrel3Noise($day, 78934) % count(self::HOT_BAR_ITEMS);

        $inventory[] = $this->createInventoryData(self::HOT_BAR_ITEMS[$hotBarIndex], true);

        $items = self::getItems(DateFunctions::isCornMoon($now));

        foreach($items as $item)
            $inventory[] = $this->createInventoryData($item, false);

        return $inventory;
    }

    private function createInventoryData($itemData, bool $special)
    {
        $item = ItemRepository::findOneByName($this->em, $itemData[0]);

        return [
            'special' => $special,
            'moneysCost' => $itemData[1],
            'recyclingCost' => ceil($itemData[1] / 2),
            'item' => [
                'name' => $item->getName(),
                'image' => $item->getImage()
            ]
        ];
    }
}