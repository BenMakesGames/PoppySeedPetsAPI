<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\CalendarFunctions;
use App\Functions\DateFunctions;
use App\Repository\ItemRepository;

class FloristService
{
    private ItemRepository $itemRepository;
    private Clock $clock;

    public function __construct(
        ItemRepository $itemRepository, Clock $clock
    )
    {
        $this->itemRepository = $itemRepository;
        $this->clock = $clock;
    }

    public function getInventory(User $user): array
    {
        $flowerbomb = $this->itemRepository->deprecatedFindOneByName('Flowerbomb');
        $fullMoonName = DateFunctions::getFullMoonName($this->clock->now);

        $inventory = [
            [
                'item' => [ 'name' => $flowerbomb->getName(), 'image' => $flowerbomb->getImage() ],
                'cost' => ($fullMoonName === 'Flower' || CalendarFunctions::isAprilFools($this->clock->now)) ? 75 : 150,
            ]
        ];

        if(CalendarFunctions::isAprilFools($this->clock->now))
        {
            $glitterBomb = $this->itemRepository->deprecatedFindOneByName('Glitter Bomb');

            $inventory[] = [
                'item' => [ 'name' => $glitterBomb->getName(), 'image' => $glitterBomb->getImage() ],
                'cost' => 20
            ];

            $jestersCap = $this->itemRepository->deprecatedFindOneByName('Jester\'s Cap');

            $inventory[] = [
                'item' => [ 'name' => $jestersCap->getName(), 'image' => $jestersCap->getImage() ],
                'cost' => 20
            ];

            $foolsSpice = $this->itemRepository->deprecatedFindOneByName('Fool\'s Spice');

            $inventory[] = [
                'item' => [ 'name' => $foolsSpice->getName(), 'image' => $foolsSpice->getImage() ],
                'cost' => 5
            ];
        }

        if(
            CalendarFunctions::isValentinesOrAdjacent($this->clock->now) ||
            CalendarFunctions::isWhiteDay($this->clock->now) ||
            CalendarFunctions::isEaster($this->clock->now) ||
            CalendarFunctions::isHalloween($this->clock->now)
        )
        {
            $chocolateBomb = $this->itemRepository->deprecatedFindOneByName('Chocolate Bomb');

            $inventory[] = [
                'item' => [ 'name' => $chocolateBomb->getName(), 'image' => $chocolateBomb->getImage() ],
                'cost' => 100
            ];
        }

        if(
            CalendarFunctions::isValentinesOrAdjacent($this->clock->now) ||
            CalendarFunctions::isWhiteDay($this->clock->now)
        )
        {
            $theLovelyHaberdashers = $this->itemRepository->deprecatedFindOneByName('Tile: Lovely Haberdashers');

            $inventory[] = [
                'item' => [ 'name' => $theLovelyHaberdashers->getName(), 'image' => $theLovelyHaberdashers->getImage() ],
                'cost' => 50
            ];
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::HollowEarth))
        {
            $flowerBasketTile = $this->itemRepository->deprecatedFindOneByName('Tile: Flower Basket');

            $inventory[] = [
                'item' => [ 'name' => $flowerBasketTile->getName(), 'image' => $flowerBasketTile->getImage() ],
                'cost' => 20
            ];
        }

        return $inventory;
    }
}