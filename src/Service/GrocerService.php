<?php
namespace App\Service;

use App\Functions\RandomFunctions;
use App\Repository\ItemRepository;

class GrocerService
{
    public const MAX_CAN_PURCHASE_PER_DAY = 20;

    private $calendarService;
    private $cacheHelper;
    private $itemRepository;

    public function __construct(
        CalendarService $calendarService, CacheHelper $cacheHelper, ItemRepository $itemRepository
    )
    {
        $this->calendarService = $calendarService;
        $this->cacheHelper = $cacheHelper;
        $this->itemRepository = $itemRepository;
    }

    private const ITEMS = [
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
        [ 'Wheat Flour', 4 ],
    ];

    // cost = fertilizer value + 2 + CEIL(chance_for_bonus_item / 50)
    private const SPECIALS = [
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

        $specialIndex = RandomFunctions::squirrel3Noise($day, 78934) % count(self::SPECIALS);
        $special = self::SPECIALS[$specialIndex];

        $inventory[] = $this->createInventoryData($special, true);

        foreach(self::ITEMS as $item)
            $inventory[] = $this->createInventoryData($item, false);

        return $inventory;
    }

    private function createInventoryData($itemData, bool $special)
    {
        $item = $this->itemRepository->findOneByName($itemData[0]);

        return [
            'special' => $special,
            'cost' => $itemData[1],
            'item' => [
                'name' => $item->getName(),
                'image' => $item->getImage()
            ]
        ];
    }
}