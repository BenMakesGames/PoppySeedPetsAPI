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
use App\Repository\HollowEarthTileRepository;
use Doctrine\ORM\EntityManagerInterface;

class HollowEarthService
{
    private $hollowEarthTileRepository;
    private $em;
    private $inventoryService;

    public const DICE_ITEMS = [
        'Glowing Four-sided Die' => 4,
        'Glowing Six-sided Die' => 6,
        'Glowing Eight-sided Die' => 8
    ];

    public function __construct(
        HollowEarthTileRepository $hollowEarthTileRepository, EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        $this->hollowEarthTileRepository = $hollowEarthTileRepository;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
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
            ->getQuery()
            ->getScalarResult()
        ;

        $results = [];

        foreach($dice as $die)
        {
            $results[] = [
                'sides' => self::DICE_ITEMS[$die['name']],
                'quantity' => $die['quantity'],
            ];
        }

        return $results;
    }

    public function advancePlayer(HollowEarthPlayer $player)
    {
        $moves = $player->getMovesRemaining();

        if($moves === 0)
            throw new \InvalidArgumentException('$player does not have any moves remaining!');

        if($player->getChosenPet() === null)
            throw new \InvalidArgumentException('A pet must be selected to lead the party.');

        $nextTile = $player->getCurrentTile();

        while($moves > 0)
        {
            $nextTile = $this->getNextTile($nextTile);

            $player->decreaseMovesRemaining();

            $this->enterTile($player, $nextTile);
        }

        $action = $nextTile->getEvent();

        $player
            ->setCurrentTile($nextTile)
            ->setMovesRemaining($moves)
            ->setCurrentAction($action)
        ;

        $this->doImmediateAction($player, $action);
    }

    private function getNextTile(HollowEarthTile $tile): HollowEarthTile
    {
        $x = $tile->getX();
        $y = $tile->getY();

        switch($tile->getMoveDirection())
        {
            case HollowEarthMoveDirectionEnum::NORTH: $y--; break;
            case HollowEarthMoveDirectionEnum::EAST: $x++; break;
            case HollowEarthMoveDirectionEnum::SOUTH: $y++; break;
            case HollowEarthMoveDirectionEnum::WEST: $x--; break;
            default: throw new \InvalidArgumentException('Tile #' . $tile->getId() . ' has an unknown direction "' . $tile->getMoveDirection() . '"');
        }

        return $this->hollowEarthTileRepository->findOneBy([
            'zone' => $tile->getZone(),
            'x' => $x,
            'y' => $y,
        ]);
    }

    private function enterTile(HollowEarthPlayer $player, HollowEarthTile $tile)
    {
        $player->setCurrentTile($tile);

        if($player->getMovesRemaining() === 0 || $tile->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_KEEP_MOVING)
            $this->doImmediateAction($player, $tile->getRequiredAction());
        if ($tile->getRequiredAction() === HollowEarthRequiredActionEnum::YES_AND_STOP_MOVING)
        {
            $player->setMovesRemaining(0);
            $this->doImmediateAction($player, $tile->getRequiredAction());
        }
    }

    public function doImmediateAction(HollowEarthPlayer $player, $action)
    {
        if(!array_key_exists('type', $action))
            return;

        switch($action['type'])
        {
            case HollowEarthActionTypeEnum::MOVE_TO:
                $this->enterTile($player, $this->hollowEarthTileRepository->find($action['id']));
                break;
            case  HollowEarthActionTypeEnum::RECEIVE_ITEM:
                $this->inventoryService->receiveItem($action['item'], $player->getUser(), $player->getUser(), $player->getChosenPet()->getName() . ' found this while exploring the Hollow Earth.', LocationEnum::HOME);
                break;
            case HollowEarthActionTypeEnum::RECEIVE_MONEY:
                $player->getUser()->increaseMoneys($action['amount']);
                break;
        }
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