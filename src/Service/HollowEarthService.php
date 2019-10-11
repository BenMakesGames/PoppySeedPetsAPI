<?php
namespace App\Service;

use App\Entity\HollowEarthPlayer;
use App\Entity\HollowEarthTile;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\User;
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
    private $responseService;

    public const DICE_ITEMS = [
        'Glowing Four-sided Die' => 4,
        'Glowing Six-sided Die' => 6,
        'Glowing Eight-sided Die' => 8
    ];

    public function __construct(
        HollowEarthTileRepository $hollowEarthTileRepository, EntityManagerInterface $em, InventoryService $inventoryService,
        PetService $petService, ResponseService $responseService
    )
    {
        $this->hollowEarthTileRepository = $hollowEarthTileRepository;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->responseService = $responseService;
    }

    public function unlockHollowEarth(User $user)
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
            ->select('item.name,COUNT(i.id) AS quantity')
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
                'sides' => self::DICE_ITEMS[$die['name']],
                'quantity' => (int)$die['quantity'],
            ];
        }

        return $results;
    }

    public function advancePlayer(HollowEarthPlayer $player)
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

        $this->doImmediateEvent($player, $action);
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

    private function enterTile(HollowEarthPlayer $player, HollowEarthTile $tile)
    {
        $player->setCurrentTile($tile);

        if($player->getMovesRemaining() === 0 || $tile->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_KEEP_MOVING)
            $this->doImmediateEvent($player, $tile->getEvent());
        if ($tile->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_STOP_MOVING)
        {
            $player->setMovesRemaining(0);
            $this->doImmediateEvent($player, $tile->getEvent());
        }
    }

    public function doImmediateEvent(HollowEarthPlayer $player, $event)
    {
        if(!array_key_exists('type', $event))
            return;

        $pet = $player->getChosenPet();

        $petChanges = new PetChanges($pet);

        switch($event['type'])
        {
            case HollowEarthActionTypeEnum::PAY_MONEY:
            case HollowEarthActionTypeEnum::PAY_ITEM:
            case HollowEarthActionTypeEnum::CHOOSE_ONE:
                $player->setCurrentAction($event);
                break;

            case HollowEarthActionTypeEnum::CHANGE_DIRECTION:
                $player->setCurrentDirection($event['direction']);
                break;

            case HollowEarthActionTypeEnum::MOVE_TO:
                $this->enterTile($player, $this->hollowEarthTileRepository->find($event['id']));
                break;

            case  HollowEarthActionTypeEnum::RECEIVE_ITEM:
                if(is_array($event['item']))
                {
                    foreach($event['item'] as $itemName)
                        $this->inventoryService->receiveItem($itemName, $player->getUser(), $player->getUser(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', LocationEnum::HOME);                }
                else
                    $this->inventoryService->receiveItem($event['item'], $player->getUser(), $player->getUser(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', LocationEnum::HOME);
                break;

            case HollowEarthActionTypeEnum::RECEIVE_MONEY:
                $player->getUser()->increaseMoneys($event['amount']);
                break;
        }

        foreach([ 'food', 'safety', 'love', 'esteem' ] as $stat)
        {
            if (array_key_exists($stat, $event))
                $player->getChosenPet()->{'increase' . $stat }($event[$stat]);
        }

        if(array_key_exists('exp', $event))
            $this->petService->gainExp($pet, $event['exp']['amount'], $event['exp']['stats']);

        if($event['description'])
        {
            $description = $this->formatEventDescription($event['description'], $player);
            $this->responseService->createActivityLog($pet, $description, '', $petChanges->compare($pet));
        }
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

    public function getResponseData(User $user)
    {
        $dice = $this->getDice($user);

        $data = [
            'player' => $user->getHollowEarthPlayer(),
            'dice' => $dice,
        ];

        return $data;
    }
}