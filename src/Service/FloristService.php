<?php
namespace App\Service;

use App\Entity\User;
use App\Functions\DateFunctions;
use App\Repository\ItemRepository;

class FloristService
{
    private $calendarService;
    private $itemRepository;

    public function __construct(CalendarService $calendarService, ItemRepository $itemRepository)
    {
        $this->calendarService = $calendarService;
        $this->itemRepository = $itemRepository;
    }

    public function getInventory(User $user): array
    {
        $now = new \DateTimeImmutable();
        $flowerbomb = $this->itemRepository->findOneByName('Flowerbomb');
        $fullMoonName = DateFunctions::getFullMoonName($now);

        $inventory = [
            [
                'item' => [ 'name' => $flowerbomb->getName(), 'image' => $flowerbomb->getImage() ],
                'cost' => $fullMoonName === 'Flower' ? 75 : 150,
            ]
        ];

        if(
            $this->calendarService->isValentines() ||
            $this->calendarService->isWhiteDay() ||
            $this->calendarService->isEaster() ||
            $this->calendarService->isHalloween()
        )
        {
            $chocolateBomb = $this->itemRepository->findOneByName('Chocolate Bomb');

            $inventory[] = [
                'item' => [ 'name' => $chocolateBomb->getName(), 'image' => $chocolateBomb->getImage() ],
                'cost' => 100
            ];
        }

        if(
            $this->calendarService->isValentines() ||
            $this->calendarService->isWhiteDay()
        )
        {
            $theLovelyHaberdashers = $this->itemRepository->findOneByName('Tile: Lovely Haberdashers');

            $inventory[] = [
                'item' => [ 'name' => $theLovelyHaberdashers->getName(), 'image' => $theLovelyHaberdashers->getImage() ],
                'cost' => 50
            ];
        }

        if($user->getUnlockedHollowEarth())
        {
            $flowerBasketTile = $this->itemRepository->findOneByName('Tile: Flower Basket');

            $inventory[] = [
                'item' => [ 'name' => $flowerBasketTile->getName(), 'image' => $flowerBasketTile->getImage() ],
                'cost' => 20
            ];
        }

        return $inventory;
    }
}