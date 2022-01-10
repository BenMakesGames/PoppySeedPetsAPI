<?php
namespace App\Service;

use App\Entity\HollowEarthPlayer;
use App\Entity\HollowEarthTile;
use App\Entity\HollowEarthTileCard;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\HollowEarthMoveDirectionEnum;
use App\Enum\HollowEarthRequiredActionEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\HollowEarthPlayerTileRepository;
use App\Repository\HollowEarthTileRepository;
use Doctrine\ORM\EntityManagerInterface;

class HollowEarthService
{
    private HollowEarthTileRepository $hollowEarthTileRepository;
    private $em;
    private $inventoryService;
    private $petExperienceService;
    private $transactionService;
    private $hollowEarthPlayerTileRepository;
    private $statusEffectService;

    public const DICE_ITEMS = [
        'Dreidel' => 4,
        'Glowing Four-sided Die' => 4,
        'Glowing Six-sided Die' => 6,
        'Glowing Eight-sided Die' => 8,
        'Glowing Twenty-sided Die' => 20,
    ];

    public function __construct(
        HollowEarthTileRepository $hollowEarthTileRepository, EntityManagerInterface $em, InventoryService $inventoryService,
        PetExperienceService $petExperienceService, TransactionService $transactionService,
        HollowEarthPlayerTileRepository $hollowEarthPlayerTileRepository, StatusEffectService $statusEffectService
    )
    {
        $this->hollowEarthTileRepository = $hollowEarthTileRepository;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->hollowEarthPlayerTileRepository = $hollowEarthPlayerTileRepository;
        $this->statusEffectService = $statusEffectService;
    }

    public function unlockHollowEarth(User $user): void
    {
        if($user->getUnlockedHollowEarth() === null)
            $user->setUnlockedHollowEarth();

        if($user->getHollowEarthPlayer() !== null)
            return;

        $hollowEarthPlayer = (new HollowEarthPlayer())
            ->setCurrentTile($this->hollowEarthTileRepository->find(1))
        ;

        $user->setHollowEarthPlayer($hollowEarthPlayer);
    }

    public function getAllCardIdsOnMap(User $player): array
    {
        $map = $this->hollowEarthTileRepository->findAll();
        $playerTiles = $this->hollowEarthPlayerTileRepository->findBy([ 'player' => $player ]);
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
        $map = $this->hollowEarthTileRepository->findAllInBounds();
        $playerTiles = $this->hollowEarthPlayerTileRepository->findBy([ 'player' => $player ]);
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
            ];
        }

        return $data;
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
     * @param HollowEarthPlayer $player
     * @param int $id
     * @throws EnumInvalidValueException
     */
    public function moveTo(HollowEarthPlayer $player, int $id): void
    {
        if($id == -1)
            $tile = $this->hollowEarthTileRepository->findRandom();
        else
            $tile = $this->hollowEarthTileRepository->find($id);

        if(!$tile)
            throw new \InvalidArgumentException('No tile found for id #' . $id);

        $this->enterTile($player, $tile);

        $card = $this->getEffectiveTileCard($player, $tile);
        $event = $card ? $card->getEvent() : null;

        $player
            ->setCurrentTile($tile)
            ->setCurrentAction($event)
        ;
    }

    /**
     * @param HollowEarthPlayer $player
     * @throws EnumInvalidValueException
     */
    public function advancePlayer(HollowEarthPlayer $player): void
    {
        if($player->getMovesRemaining() === 0)
            throw new \InvalidArgumentException('$player does not have any moves remaining!');

        if($player->getChosenPet() === null)
            throw new \InvalidArgumentException('A pet must be selected to lead the party.');

        $nextTile = $player->getCurrentTile();

        while($player->getMovesRemaining() > 0 && $player->getCurrentAction() === null)
        {
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
    }

    private function getNextTile(HollowEarthPlayer $player): HollowEarthTile
    {
        $x = $player->getCurrentTile()->getX();
        $y = $player->getCurrentTile()->getY();

        switch($player->getCurrentDirection())
        {
            case HollowEarthMoveDirectionEnum::NORTH: $y--; break;
            case HollowEarthMoveDirectionEnum::EAST: $x++; break;
            case HollowEarthMoveDirectionEnum::SOUTH: $y++; break;
            case HollowEarthMoveDirectionEnum::WEST: $x--; break;
            case HollowEarthMoveDirectionEnum::ZERO: break;
            default: throw new \InvalidArgumentException('Player has an unknown currentDirection: "' . $player->getCurrentDirection() . '"');
        }

        return $this->hollowEarthTileRepository->findOneBy([
            'x' => $x,
            'y' => $y,
        ]) ?? $this->hollowEarthTileRepository->find(53);
    }

    /**
     * @param HollowEarthPlayer $player
     * @param HollowEarthTile $tile
     * @throws EnumInvalidValueException
     */
    private function enterTile(HollowEarthPlayer $player, HollowEarthTile $tile): void
    {
        $player->setCurrentTile($tile);

        $card = $this->getEffectiveTileCard($player, $tile);

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

    private function getEffectiveTileCard(HollowEarthPlayer $player, HollowEarthTile $tile): ?HollowEarthTileCard
    {
        $playerTile = $this->hollowEarthPlayerTileRepository->findOneBy([
            'player' => $player->getUser(),
            'tile' => $tile,
        ]);

        return $playerTile ? $playerTile->getCard() : $tile->getCard();
    }

    /**
     * @param HollowEarthPlayer $player
     * @param array $event
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

        if(array_key_exists('exp', $event))
        {
            $this->petExperienceService->gainExp($pet, $event['exp']['amount'], $event['exp']['stats']);
            $doLog = true;
        }

        if(array_key_exists('statusEffect', $event))
        {
            $this->statusEffectService->applyStatusEffect($pet, $event['statusEffect']['status'], $event['statusEffect']['duration']);
            $doLog = true;
        }

        $activityLog = null;

        if(array_key_exists('description', $event) && $doLog)
        {
            $description = $this->formatEventDescription($event['description'], $player);

            $currentCard = $this->getEffectiveTileCard($player, $player->getCurrentTile());

            $activityLog = (new PetActivityLog())
                ->setPet($pet)
                ->setEntry($description)
                ->setIcon(($currentCard && $currentCard->getImage()) ? ('hollow-earth/tile/' . $currentCard->getImage()) : '')
                ->setChanges($petChanges->compare($pet))
                ->setViewed()
            ;

            $this->em->persist($activityLog);
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
    }

    private function receiveItems(HollowEarthPlayer $player, Pet $pet, PetChanges $petChanges, $items, ?PetActivityLog $activityLog)
    {
        if(!is_array($items))
            $items = [ $items ];

        if($activityLog == null)
        {
            $currentCard = $this->getEffectiveTileCard($player, $player->getCurrentTile());

            $activityLog = (new PetActivityLog())
                ->setPet($player->getChosenPet())
                ->setEntry('While exploring the Hollow Earth, ' . $player->getChosenPet()->getName() . ' received ' . ArrayFunctions::list_nice($items) . '.')
                ->setIcon(($currentCard && $currentCard->getImage()) ? ('hollow-earth/tile/' . $currentCard->getImage()) : '')
                ->setChanges($petChanges->compare($pet))
                ->setViewed()
            ;

            $this->em->persist($activityLog);
        }

        foreach($items as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $player->getChosenPet(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', $activityLog);
    }

    public function formatEventDescription(string $description, HollowEarthPlayer $player): string
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
}
