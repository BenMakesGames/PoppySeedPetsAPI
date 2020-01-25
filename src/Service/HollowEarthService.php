<?php
namespace App\Service;

use App\Entity\HollowEarthPlayer;
use App\Entity\HollowEarthTile;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\HollowEarthMoveDirectionEnum;
use App\Enum\HollowEarthRequiredActionEnum;
use App\Enum\LocationEnum;
use App\Model\PetChanges;
use App\Repository\HollowEarthTileRepository;
use Doctrine\ORM\EntityManagerInterface;

class HollowEarthService
{
    private $hollowEarthTileRepository;
    private $em;
    private $inventoryService;
    private $petService;
    private $petExperienceService;
    private $transactionService;

    public const DICE_ITEMS = [
        'Dreidel' => 4,
        'Glowing Four-sided Die' => 4,
        'Glowing Six-sided Die' => 6,
        'Glowing Eight-sided Die' => 8
    ];

    public function __construct(
        HollowEarthTileRepository $hollowEarthTileRepository, EntityManagerInterface $em, InventoryService $inventoryService,
        PetService $petService, PetExperienceService $petExperienceService, TransactionService $transactionService
    )
    {
        $this->hollowEarthTileRepository = $hollowEarthTileRepository;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
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

    public function getDice(User $user): array
    {
        $dice = $this->em->createQueryBuilder()
            ->select('item.name,item.image,COUNT(i.id) AS quantity')
            ->from(Inventory::class, 'i')
            ->leftJoin('i.item', 'item')
            ->andWhere('i.owner=:owner')
            ->andWhere('item.name IN (:dice)')
            ->setParameter('owner', $user->getId())
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
        $tile = $this->hollowEarthTileRepository->find($id);

        if(!$tile)
            throw new \InvalidArgumentException('No tile found for id #' . $id);

        $this->enterTile($player, $tile);

        $player
            ->setCurrentTile($tile)
            ->setCurrentAction($tile->getEvent())
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

        $action = $nextTile->getEvent();

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
            default: throw new \InvalidArgumentException('Player has an unknown currentDirection: "' . $player->getCurrentDirection() . '"');
        }

        return $this->hollowEarthTileRepository->findOneBy([
            'zone' => $player->getCurrentTile()->getZone(),
            'x' => $x,
            'y' => $y,
        ]);
    }

    /**
     * @param HollowEarthPlayer $player
     * @param HollowEarthTile $tile
     * @throws EnumInvalidValueException
     */
    private function enterTile(HollowEarthPlayer $player, HollowEarthTile $tile): void
    {
        $player->setCurrentTile($tile);

        if($player->getMovesRemaining() === 0 || $tile->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_KEEP_MOVING)
            $this->doImmediateEvent($player, $tile->getEvent());
        else if ($tile->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_STOP_MOVING)
        {
            $player->setMovesRemaining(0);
            $this->doImmediateEvent($player, $tile->getEvent());
        }
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
            $this->petExperienceService->applyStatusEffect($pet, $event['statusEffect']['status'], $event['statusEffect']['duration'], $event['statusEffect']['maxDuration']);
            $doLog = true;
        }

        if(array_key_exists('description', $event) && $doLog)
        {
            $description = $this->formatEventDescription($event['description'], $player);

            $activityLog = (new PetActivityLog())
                ->setPet($pet)
                ->setEntry($description)
                ->setIcon('')
                ->setChanges($petChanges->compare($pet))
            ;

            $this->em->persist($activityLog);
        }

        if(array_key_exists('receiveItems', $event))
        {
            if(is_array($event['receiveItems']))
            {
                foreach($event['receiveItems'] as $itemName)
                    $this->inventoryService->receiveItem($itemName, $player->getUser(), $player->getUser(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', LocationEnum::HOME);
            }
            else
                $this->inventoryService->receiveItem($event['receiveItems'], $player->getUser(), $player->getUser(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', LocationEnum::HOME);
        }

        // because I'm likely to screw up and forget to make it plural:
        if(array_key_exists('receiveItem', $event))
        {
            if(is_array($event['receiveItem']))
            {
                foreach($event['receiveItem'] as $itemName)
                    $this->inventoryService->receiveItem($itemName, $player->getUser(), $player->getUser(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', LocationEnum::HOME);
            }
            else
                $this->inventoryService->receiveItem($event['receiveItem'], $player->getUser(), $player->getUser(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', LocationEnum::HOME);
        }

        if(array_key_exists('receiveMoneys', $event))
        {
            $this->transactionService->getMoney($player->getUser(), $event['receiveMoneys'], 'Received this while exploring the Hollow Earth.');
        }

        if(array_key_exists('changeDirection', $event))
            $player->setCurrentDirection($event['changeDirection']);

        if(array_key_exists('type', $event))
            $player->setCurrentAction($event);
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
        $dice = $this->getDice($user);

        return [
            'player' => $user->getHollowEarthPlayer(),
            'dice' => $dice,
        ];
    }
}