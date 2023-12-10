<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\CalendarFunctions;
use App\Functions\DateFunctions;
use App\Functions\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class FloristService
{
    public function __construct(
        private readonly EntityManagerInterface $em, private readonly Clock $clock
    )
    {
    }

    public function getInventory(User $user): array
    {
        $flowerbomb = ItemRepository::findOneByName($this->em, 'Flowerbomb');
        $fullMoonName = DateFunctions::getFullMoonName($this->clock->now);

        $inventory = [
            [
                'item' => [ 'name' => $flowerbomb->getName(), 'image' => $flowerbomb->getImage() ],
                'cost' => ($fullMoonName === 'Flower' || CalendarFunctions::isAprilFools($this->clock->now)) ? 75 : 150,
            ]
        ];

        if(CalendarFunctions::isAprilFools($this->clock->now))
        {
            $glitterBomb = ItemRepository::findOneByName($this->em, 'Glitter Bomb');

            $inventory[] = [
                'item' => [ 'name' => $glitterBomb->getName(), 'image' => $glitterBomb->getImage() ],
                'cost' => 20
            ];

            $jestersCap = ItemRepository::findOneByName($this->em, 'Jester\'s Cap');

            $inventory[] = [
                'item' => [ 'name' => $jestersCap->getName(), 'image' => $jestersCap->getImage() ],
                'cost' => 20
            ];

            $foolsSpice = ItemRepository::findOneByName($this->em, 'Fool\'s Spice');

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
            $chocolateBomb = ItemRepository::findOneByName($this->em, 'Chocolate Bomb');

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
            $theLovelyHaberdashers = ItemRepository::findOneByName($this->em, 'Tile: Lovely Haberdashers');

            $inventory[] = [
                'item' => [ 'name' => $theLovelyHaberdashers->getName(), 'image' => $theLovelyHaberdashers->getImage() ],
                'cost' => 50
            ];
        }

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::HollowEarth))
        {
            $flowerBasketTile = ItemRepository::findOneByName($this->em, 'Tile: Flower Basket');

            $inventory[] = [
                'item' => [ 'name' => $flowerBasketTile->getName(), 'image' => $flowerBasketTile->getImage() ],
                'cost' => 20
            ];
        }

        return $inventory;
    }
}