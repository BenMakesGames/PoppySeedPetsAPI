<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\HollowEarthPlayer;
use App\Entity\HollowEarthPlayerTile;
use App\Entity\HollowEarthTile;
use App\Entity\HollowEarthTileCard;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\HollowEarthMoveDirectionEnum;
use App\Enum\HollowEarthRequiredActionEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\HollowEarthTileRepository;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\PetChanges;
use Doctrine\ORM\EntityManagerInterface;

class HollowEarthService
{
    public const int LOST_IN_TIME_AND_SPACE_TILE_ID = 53;

    public const array DICE_ITEMS = [
        'One-sided Die' => 1,
        'Dreidel' => 4,
        'Glowing Four-sided Die' => 4,
        'Glowing Six-sided Die' => 6,
        'Glowing Eight-sided Die' => 8,
        'Glowing Ten-sided Die' => 10,
        'Glowing Twelve-sided Die' => 12,
        'Glowing Twenty-sided Die' => 20,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly TransactionService $transactionService,
        private readonly ResponseService $responseService,
        private readonly UserStatsService $userStatsRepository,
        private readonly IRandom $rng,
        private readonly CommentFormatter $commentFormatter
    )
    {
    }

    public function unlockHollowEarth(User $user): void
    {
        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::HollowEarth))
            UserUnlockedFeatureHelpers::create($this->em, $user, UnlockableFeatureEnum::HollowEarth);

        if($user->getHollowEarthPlayer() !== null)
            return;

        $hollowEarthPlayer = (new HollowEarthPlayer(user: $user))
            ->setCurrentTile(HollowEarthTileRepository::findOneById($this->em, 1))
        ;

        $user->setHollowEarthPlayer($hollowEarthPlayer);
    }

    public function getAllCardIdsOnMap(User $player): array
    {
        $map = $this->em->getRepository(HollowEarthTile::class)->findAll();
        $playerTiles = $this->em->getRepository(HollowEarthPlayerTile::class)->findBy([ 'player' => $player ]);
        $playerTilesByTile = [];

        foreach($playerTiles as $t)
            $playerTilesByTile[$t->getTile()->getId()] = $t;

        $data = [];

        foreach($map as $tile)
        {
            $playerTile = array_key_exists($tile->getId(), $playerTilesByTile)
                ? $playerTilesByTile[$tile->getId()]
                : null
            ;

            $card = $playerTile ? $playerTile->getCard() : $tile->getCard();

            if($card)
                $data[] = $card->getId();
        }

        return $data;

    }

    public function getMap(User $player): array
    {
        $map = HollowEarthTileRepository::findAllInBounds($this->em);
        $playerTiles = $this->em->getRepository(HollowEarthPlayerTile::class)->findBy([ 'player' => $player ]);
        $playerTilesByTile = [];

        foreach($playerTiles as $t)
            $playerTilesByTile[$t->getTile()->getId()] = $t;

        $data = [];

        foreach($map as $tile)
        {
            $playerTile = array_key_exists($tile->getId(), $playerTilesByTile)
                ? $playerTilesByTile[$tile->getId()]
                : null
            ;

            $card = $playerTile ? $playerTile->getCard() : $tile->getCard();

            $data[] = [
                'id' => $tile->getId(),
                'x' => $tile->getX(),
                'y' => $tile->getY(),
                'name' => $card ? $card->getName() : null,
                'image' => $card ? $card->getImage() : null,
                'fixed' => $tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed',
                'types' => $tile->getTypes()->map(fn($t) => $t->getName()),
                'availableGoods' => $tile->getGoods(),
                'goodsSide' => $tile->getGoodsSide(),
                'selectedGoods' => $playerTile ? $playerTile->getGoods() : null,
                'isTradingDepot' => $tile->getIsTradingDepot(),
                'author' => $this->getCardAuthor($card)
            ];
        }

        return $data;
    }

    private function getCardAuthor(?HollowEarthTileCard $card): ?array
    {
        if(!$card?->getAuthor())
            return null;

        $authorIds = $this->commentFormatter->getUserIds($card->getAuthor());

        return [
            'name' => $this->commentFormatter->format($card->getAuthor()),
            'id' => count($authorIds) > 0 ? $authorIds[0] : null,
        ];
    }

    public function getDice(User $user): array
    {
        $dice = $this->em->createQueryBuilder()
            ->select('item.name,item.image,COUNT(i.id) AS quantity')
            ->from(Inventory::class, 'i')
            ->leftJoin('i.item', 'item')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:allowedLocations)')
            ->andWhere('item.name IN (:dice)')
            ->setParameter('owner', $user->getId())
            ->setParameter('allowedLocations', Inventory::CONSUMABLE_LOCATIONS)
            ->setParameter('dice', array_keys(self::DICE_ITEMS))
            ->groupBy('item.name')
            ->getQuery()
            ->getScalarResult()
        ;

        $results = [];

        foreach($dice as $die)
        {
            if($die['name'] === NULL || (int)$die['quantity'] === 0) // why does this happen sometimes??
                continue;

            $results[] = [
                'item' => $die['name'],
                'image' => $die['image'],
                'size' => self::DICE_ITEMS[$die['name']],
                'quantity' => (int)$die['quantity'],
            ];
        }

        return $results;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function moveTo(HollowEarthPlayer $player, int $id): void
    {
        if($id == -1)
            $tile = HollowEarthTileRepository::findRandom($this->em, $this->rng);
        else
            $tile = HollowEarthTileRepository::findOneById($this->em, $id);

        if(!$tile)
            throw new \Exception('No tile found for id #' . $id);

        $this->enterTile($player, $tile);

        $card = $this->getEffectiveTileCard($player, $tile);
        $event = $card?->getEvent();

        $player
            ->setCurrentTile($tile)
            ->setCurrentAction($event)
        ;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function advancePlayer(HollowEarthPlayer $player): void
    {
        if($player->getMovesRemaining() === 0)
            throw new \InvalidArgumentException('$player does not have any moves remaining!');

        if($player->getChosenPet() === null)
            throw new \InvalidArgumentException('A pet must be selected to lead the party.');

        $nextTile = $player->getCurrentTile();

        $movesRemaining = $player->getMovesRemaining();
        $leftGoTile = false;

        while($player->getMovesRemaining() > 0 && $player->getCurrentAction() === null)
        {
            $leftGoTile = $leftGoTile || ($player->getCurrentTile()->getCard()?->getId() === 1);

            $nextTile = $this->getNextTile($player);

            $player->decreaseMovesRemaining();

            $this->enterTile($player, $nextTile);
        }

        $card = $this->getEffectiveTileCard($player, $nextTile);

        $action = $card ? $card->getEvent() : null;

        $player
            ->setCurrentTile($nextTile)
            ->setCurrentAction($action)
        ;

        $this->userStatsRepository->incrementStat($player->getUser(), UserStatEnum::HOLLOW_EARTH_SPACES_MOVED, $movesRemaining - $player->getMovesRemaining());

        if($leftGoTile)
            PetBadgeHelpers::awardBadgeAndLog($this->em, $player->getChosenPet(), PetBadgeEnum::GO, null);
    }

    private function getNextTile(HollowEarthPlayer $player): HollowEarthTile
    {
        $x = $player->getCurrentTile()->getX();
        $y = $player->getCurrentTile()->getY();

        switch($player->getCurrentDirection())
        {
            case HollowEarthMoveDirectionEnum::North: $y--; break;
            case HollowEarthMoveDirectionEnum::East: $x++; break;
            case HollowEarthMoveDirectionEnum::South: $y++; break;
            case HollowEarthMoveDirectionEnum::West: $x--; break;
            case HollowEarthMoveDirectionEnum::Zero: break;
            default: throw new \InvalidArgumentException('Player has an unknown currentDirection: "' . $player->getCurrentDirection()->value . '"');
        }

        return $this->em->getRepository(HollowEarthTile::class)->findOneBy([
            'x' => $x,
            'y' => $y,
        ]) ?? HollowEarthTileRepository::findOneById($this->em, self::LOST_IN_TIME_AND_SPACE_TILE_ID);
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function enterTile(HollowEarthPlayer $player, HollowEarthTile $tile): void
    {
        $player->setCurrentTile($tile);

        $playerTile = $this->getCurrentPlayerTile($player, $tile);

        if($playerTile && $playerTile->getGoods())
        {
            $this->collectGoods($player, $playerTile->getGoods(), $player->getMovesRemaining() === 0);
        }

        $card = $playerTile ? $playerTile->getCard() : $tile->getCard();

        if(!$card)
            return;

        if($player->getMovesRemaining() === 0 || $card->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_KEEP_MOVING)
            $this->doImmediateEvent($player, $card->getEvent());
        else if ($card->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_STOP_MOVING)
        {
            $player->setMovesRemaining(0);
            $this->doImmediateEvent($player, $card->getEvent());
        }
    }

    private function collectGoods(HollowEarthPlayer $player, string $goods, bool $getDouble): void
    {
        $quantity = $getDouble ? 2 : 1;

        switch($goods)
        {
            case 'jade': $player->increaseJade($quantity); break;
            case 'incense': $player->increaseIncense($quantity); break;
            case 'salt': $player->increaseSalt($quantity); break;
            case 'amber': $player->increaseAmber($quantity); break;
            case 'fruit': $player->increaseFruit($quantity); break;
            default: throw new \InvalidArgumentException('Unknown good type.');
        }

        $this->responseService->addFlashMessage('Collected ' . $quantity . ' ' . ucfirst($goods) . '.');

        $player->setShowGoods();
    }

    private function getCurrentPlayerTile(HollowEarthPlayer $player, HollowEarthTile $tile): ?HollowEarthPlayerTile
    {
        return $this->em->getRepository(HollowEarthPlayerTile::class)->findOneBy([
            'player' => $player->getUser(),
            'tile' => $tile,
        ]);
    }

    private function getEffectiveTileCard(HollowEarthPlayer $player, HollowEarthTile $tile): ?HollowEarthTileCard
    {
        $playerTile = $this->getCurrentPlayerTile($player, $tile);

        return $playerTile ? $playerTile->getCard() : $tile->getCard();
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function doImmediateEvent(HollowEarthPlayer $player, array $event): void
    {
        $doLog = false;
        $pet = $player->getChosenPet();
        $petChanges = new PetChanges($pet);

        foreach([ 'food', 'safety', 'love', 'esteem' ] as $stat)
        {
            if (array_key_exists($stat, $event))
            {
                $player->getChosenPet()->{'increase' . $stat}($event[$stat]);
                $doLog = true;
            }
        }

        if(array_key_exists('statusEffect', $event))
        {
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, $event['statusEffect']['status'], $event['statusEffect']['duration']);
            $doLog = true;
        }

        if(array_key_exists('exp', $event))
            $doLog = true;

        $activityLog = null;

        if(array_key_exists('description', $event) && $doLog)
        {
            $description = self::formatEventDescription($event['description'], $player);

            $currentCard = $this->getEffectiveTileCard($player, $player->getCurrentTile());

            $activityLog = PetActivityLogFactory::createReadLog($this->em, $pet, $description)
                ->setIcon(($currentCard && $currentCard->getImage()) ? ('hollow-earth/tile/' . $currentCard->getImage()) : '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Hollow Earth' ]))
            ;
        }

        if(array_key_exists('exp', $event))
        {
            // old tiles refer to the "umbra" skill, but that is no longer a skill; it was renamed to arcana, so:
            $stats = array_map(fn($stat) => $stat === 'umbra' ? 'arcana' : $stat, $event['exp']['stats']);

            $this->petExperienceService->gainExp($pet, $event['exp']['amount'], $stats, $activityLog);
        }

        if(array_key_exists('receiveItems', $event))
            $this->receiveItems($player, $pet, $petChanges, $event['receiveItems'], $activityLog);

        // because I'm likely to screw up and forget to make it plural:
        if(array_key_exists('receiveItem', $event))
            $this->receiveItems($player, $pet, $petChanges, $event['receiveItem'], $activityLog);

        if(array_key_exists('receiveMoneys', $event))
            $this->transactionService->getMoney($player->getUser(), $event['receiveMoneys'], $player->getChosenPet()->getName() . ' got this while exploring the Hollow Earth.');

        if(array_key_exists('changeDirection', $event))
            $player->setCurrentDirection($event['changeDirection']);

        if(array_key_exists('type', $event))
            $player->setCurrentAction($event);

        if($activityLog)
            $activityLog->setChanges($petChanges->compare($pet));
    }

    /**
     * @param string[]|string $items
     */
    private function receiveItems(HollowEarthPlayer $player, Pet $pet, PetChanges $petChanges, array|string $items, ?PetActivityLog $activityLog): void
    {
        if(!is_array($items))
            $items = [ $items ];

        if($activityLog == null)
        {
            $currentCard = $this->getEffectiveTileCard($player, $player->getCurrentTile());

            $activityLog = PetActivityLogFactory::createReadLog($this->em, $player->getChosenPet(), 'While exploring the Hollow Earth, ' . $player->getChosenPet()->getName() . ' received ' . ArrayFunctions::list_nice_sorted($items) . '.')
                ->setIcon(($currentCard && $currentCard->getImage()) ? ('hollow-earth/tile/' . $currentCard->getImage()) : '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Hollow Earth' ]))
                ->setChanges($petChanges->compare($pet))
            ;
        }

        foreach($items as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $player->getChosenPet(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', $activityLog);
    }

    public static function formatEventDescription(string $description, HollowEarthPlayer $player): string
    {
        $replacements = [
            '%pet.name%' => $player->getChosenPet()->getName(),
            '%player.name%' => $player->getUser()->getName(),
        ];

        return str_replace(
            array_keys($replacements),
            $replacements,
            $description
        );
    }

    public function getResponseData(User $user): array
    {
        $map = $this->getMap($user);
        $dice = $this->getDice($user);

        return [
            'player' => $user->getHollowEarthPlayer(),
            'map' => $map,
            'dice' => $dice,
        ];
    }

    public function getTrade(HollowEarthPlayer $player, string $tradeId)
    {
        return ArrayFunctions::find_one($this->getTrades($player), fn($t) => $t['id'] === $tradeId);
    }

    public function getTrades(HollowEarthPlayer $player): array
    {
        $items = ItemRepository::findByNames($this->em, [
            'Potion of Arcana',
            'Potion of Brawling',
            'Potion of Crafts',
            'Potion of Music',
            'Potion of Nature',
            'Potion of Science',
            'Potion of Stealth',
            'Fruits & Veggies Box',
            'Small Box of Ores',
            'Magic Smoke',
            'Quintessence',
            'Bag of Beans',
        ]);

        return [
            self::createTrade($player, $items, 'skillpotion7', 'Potion of Arcana', [ 'incense' => 4, 'amber' => 4 ]),
            self::createTrade($player, $items, 'skillpotion1', 'Potion of Brawling', [ 'jade' => 4, 'fruit' => 4 ]),
            self::createTrade($player, $items, 'skillpotion2', 'Potion of Crafts', [ 'jade' => 4, 'incense' => 4 ]),
            self::createTrade($player, $items, 'skillpotion3', 'Potion of Music', [ 'incense' => 4, 'fruit' => 4 ]),
            self::createTrade($player, $items, 'skillpotion4', 'Potion of Nature', [ 'fruit' => 4, 'salt' => 4 ]),
            self::createTrade($player, $items, 'skillpotion5', 'Potion of Science', [ 'amber' => 4, 'salt' => 4 ]),
            self::createTrade($player, $items, 'skillpotion6', 'Potion of Stealth', [ 'incense' => 4, 'salt' => 4 ]),
            self::createTrade($player, $items, 'magic1', 'Magic Smoke', [ 'jade' => 2 ]),
            self::createTrade($player, $items, 'magic2', 'Quintessence', [ 'incense' => 2 ]),
            self::createTrade($player, $items, 'box2', 'Small Box of Ores', [ 'salt' => 2 ]),
            self::createTrade($player, $items, 'box1', 'Fruits & Veggies Box', [ 'amber' => 2 ]),
            self::createTrade($player, $items, 'box3', 'Bag of Beans', [ 'fruit' => 2 ]),
        ];
    }

    private static function createTrade(HollowEarthPlayer $player, array $items, string $id, string $itemName, array $cost): array
    {
        return
        [
            'id' => $id,
            'item' => self::serializeItem($items, $itemName),
            'cost' => $cost,
            'maxQuantity' => self::computeMaxQuantity(
                $player,
                array_key_exists('jade', $cost) ? $cost['jade'] : 0,
                array_key_exists('incense', $cost) ? $cost['incense'] : 0,
                array_key_exists('salt', $cost) ? $cost['salt'] : 0,
                array_key_exists('amber', $cost) ? $cost['amber'] : 0,
                array_key_exists('fruit', $cost) ? $cost['fruit'] : 0
            ),
        ];
    }

    private static function computeMaxQuantity(HollowEarthPlayer $player, int $jade, int $incense, int $salt, int $amber, int $fruit): int
    {
        return min(
            $jade == 0 ? 100 : (int)floor($player->getJade() / $jade),
            $incense == 0 ? 100 : (int)floor($player->getIncense() / $incense),
            $salt == 0 ? 100 : (int)floor($player->getSalt() / $salt),
            $amber == 0 ? 100 : (int)floor($player->getAmber() / $amber),
            $fruit == 0 ? 100 : (int)floor($player->getFruit() / $fruit),
        );
    }

    /**
     * @param Item[] $items
     */
    private static function serializeItem(array $items, string $itemName): array
    {
        /** @var Item $item */
        $item = ArrayFunctions::find_one($items, fn(Item $i) => $i->getName() === $itemName);

        return [
            'name' => $item->getName(),
            'image' => $item->getImage(),
        ];
    }
}
