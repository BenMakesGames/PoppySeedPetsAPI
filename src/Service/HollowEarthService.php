<?php
namespace App\Service;

use App\Entity\HollowEarthPlayer;
use App\Entity\HollowEarthTile;
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

    public function __construct(
        HollowEarthTileRepository $hollowEarthTileRepository, EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        $this->hollowEarthTileRepository = $hollowEarthTileRepository;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
    }

    public function advancePlayer(HollowEarthPlayer $player)
    {
        $moves = $player->getMovesRemaining();

        if($moves === 0)
            throw new \InvalidArgumentException('$player does not have any moves remaining!');

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
}