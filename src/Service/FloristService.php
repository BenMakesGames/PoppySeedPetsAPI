<?php
namespace App\Service;

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

    public function getInventory(): array
    {
        $flowerbomb = $this->itemRepository->findOneByName('Flowerbomb');

        $inventory = [
            [
                'item' => [ 'name' => $flowerbomb->getName(), 'image' => $flowerbomb->getImage() ],
                'cost' => 150
            ]
        ];

        if($this->calendarService->isValentines() || $this->calendarService->isWhiteDay() || $this->calendarService->isEaster())
        {
            $chocolateBomb = $this->itemRepository->findOneByName('Chocolate Bomb');

            $inventory[] = [
                'item' => [ 'name' => $chocolateBomb->getName(), 'image' => $chocolateBomb->getImage() ],
                'cost' => 100
            ];
        }

        return $inventory;
    }
}