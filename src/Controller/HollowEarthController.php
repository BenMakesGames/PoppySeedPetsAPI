<?php
namespace App\Controller;

use App\Entity\HollowEarthPlayer;
use App\Entity\HollowEarthPlayerTile;
use App\Entity\HollowEarthTileType;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\HollowEarthRequiredActionEnum;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\HollowEarthPlayerTileRepository;
use App\Repository\HollowEarthTileRepository;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Service\CalendarService;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class HollowEarthController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getState(ResponseService $responseService, HollowEarthService $hollowEarthService)
    {
        $user = $this->getUser();

        if($user->getHollowEarthPlayer() === null)
            throw new AccessDeniedHttpException();

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }

    /**
     * @Route("/myTiles", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyTiles(
        InventoryRepository $inventoryRepository, ResponseService $responseService, Request $request
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        $types = $request->query->get('types', []);

        if(!is_array($types) || count($types) === 0)
            throw new UnprocessableEntityHttpException('The types of tiles is missing.');

        $tiles = $inventoryRepository->findHollowEarthTiles($user, $types);

        return $responseService->success($tiles, [ SerializationGroupEnum::MY_HOLLOW_EARTH_TILES ]);
    }

    /**
     * @Route("/removeTileCard", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function removeTileCard(
        Request $request, HollowEarthPlayerTileRepository $hollowEarthPlayerTileRepository,
        ResponseService $responseService, EntityManagerInterface $em, HollowEarthTileRepository $hollowEarthTileRepository,
        InventoryRepository $inventoryRepository, HollowEarthService $hollowEarthService
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        if($player->getCurrentAction())
            throw new UnprocessableEntityHttpException('You can\'t change the map while you\'re moving!');

        $tileId = $request->request->getInt('tile', 0);

        $tile = $hollowEarthTileRepository->find($tileId);

        if(!$tile)
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth does not exist?!?! (Maybe reload and try again...)');

        if($tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed')
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth cannot be changed!');

        $existingPlayerTile = $hollowEarthPlayerTileRepository->findOneBy([
            'player' => $user,
            'tile' => $tile,
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setCard(null);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setCard(null)
            ;

            $em->persist($playerTile);
        }

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/setTileCard", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setTileCard(
        Request $request, HollowEarthPlayerTileRepository $hollowEarthPlayerTileRepository,
        ResponseService $responseService, EntityManagerInterface $em, HollowEarthTileRepository $hollowEarthTileRepository,
        InventoryRepository $inventoryRepository, HollowEarthService $hollowEarthService
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        if($player->getCurrentAction())
            throw new UnprocessableEntityHttpException('You can\'t change the map while you\'re moving!');

        $tileId = $request->request->getInt('tile', 0);
        $inventoryId = $request->request->getInt('item', 0);

        $tile = $hollowEarthTileRepository->find($tileId);

        if(!$tile)
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth does not exist?!?! (Maybe reload and try again...)');

        if($tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed')
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth cannot be changed!');

        $inventory = $inventoryRepository->findOneBy([
            'id' => $inventoryId,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$inventory)
            throw new UnprocessableEntityHttpException('That item couldn\'t be found! (Reload and try again.)');

        $card = $inventory->getItem()->getHollowEarthTileCard();

        if(!$card)
            throw new UnprocessableEntityHttpException('That item isn\'t a Hollow Earth Tile! (Weird! Reload and try again...)');

        $canUseTile = ArrayFunctions::any($tile->getTypes(), fn(HollowEarthTileType $tt) => $tt->getId() === $card->getType()->getId());

        if(!$canUseTile)
            throw new UnprocessableEntityHttpException('You can\'t use that Tile on this space! (The types don\'t match!)');

        $cardIdsOnMap = $hollowEarthService->getAllCardIdsOnMap($user);

        if(array_search($card->getId(), $cardIdsOnMap))
            throw new UnprocessableEntityHttpException('You already have that Tile on the map! (Each Tile can only appear once!)');

        $existingPlayerTile = $hollowEarthPlayerTileRepository->findOneBy([
            'player' => $user,
            'tile' => $tile,
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setCard($card);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setCard($card)
            ;

            $em->persist($playerTile);
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->success();
    }

    /**
     * @Route("/changeTileGoods", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function changeTileGoods(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        HollowEarthPlayerTileRepository $hollowEarthPlayerTileRepository
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        $selectedGoods = $request->request->getAlpha('goods');

        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getGoods() || count($tile->getGoods()) === 0)
            throw new UnprocessableEntityHttpException('You are not on a tile that produces goods.');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new UnprocessableEntityHttpException('You can\'t change goods while you\'re moving!');

        if(!in_array($selectedGoods, $tile->getGoods()))
            throw new UnprocessableEntityHttpException('This tile is not capable of producing that type of good.');

        $existingPlayerTile = $hollowEarthPlayerTileRepository->findOneBy([
            'player' => $user,
            'tile' => $tile->getId(),
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setGoods($selectedGoods);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setGoods($selectedGoods)
            ;

            $em->persist($playerTile);
        }

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/trades", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getTrades(ResponseService $responseService, HollowEarthService $hollowEarthService)
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();
        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getIsTradingDepot())
            throw new UnprocessableEntityHttpException('You are not on a trade depot!');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new UnprocessableEntityHttpException('You can\'t trade while you\'re moving!');

        $trades = $hollowEarthService->getTrades($player);

        return $responseService->success($trades);
    }

    /**
     * @Route("/trades/{tradeId}/exchange", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function makeExchange(
        string $tradeId, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        HollowEarthService $hollowEarthService, InventoryService $inventoryService,
        ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();
        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getIsTradingDepot())
            throw new UnprocessableEntityHttpException('You are not on a trade depot!');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new UnprocessableEntityHttpException('You can\'t trade while you\'re moving!');

        $trade = $hollowEarthService->getTrade($player, $tradeId);

        if(!$trade)
            throw new UnprocessableEntityHttpException('No such trade exists...');

        $quantity = $request->request->getInt('quantity', 1);

        if($trade['maxQuantity'] < $quantity)
            throw new UnprocessableEntityHttpException('You do not have enough goods to make ' . $quantity . ' trade' . ($quantity == 1 ? '' : 's') . '; you can do up to ' . $trade['maxQuantity'] . ', at most.');

        $itemsAtHome = $inventoryService->countTotalInventory($user, LocationEnum::HOME);

        $destination = LocationEnum::HOME;

        if($itemsAtHome + $quantity > User::MAX_HOUSE_INVENTORY)
        {
            if($user->getUnlockedBasement())
            {
                $destination = LocationEnum::BASEMENT;

                $itemsInBasement = $inventoryService->countTotalInventory($user, LocationEnum::BASEMENT);

                if($itemsInBasement + $quantity > User::MAX_BASEMENT_INVENTORY)
                {
                    throw new UnprocessableEntityHttpException('There is not enough room in your house or basement for ' . $quantity . ' more items!');
                }
            }
            else
            {
                throw new UnprocessableEntityHttpException('There is not enough room in your house for ' . $quantity . ' more items!');
            }
        }

        $item = $itemRepository->findOneByName($trade['item']['name']);

        if(!$item)
            throw new \Exception('No item called "' . $trade['item']['name'] . '" exists in the database!');

        for($i = 0; $i < $quantity; $i++)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' traded for this in the Portal.', $destination);

        if(array_key_exists('jade', $trade['cost'])) $player->increaseJade(-$trade['cost']['jade'] * $quantity);
        if(array_key_exists('incense', $trade['cost'])) $player->increaseIncense(-$trade['cost']['incense'] * $quantity);
        if(array_key_exists('amber', $trade['cost'])) $player->increaseAmber(-$trade['cost']['amber'] * $quantity);
        if(array_key_exists('salt', $trade['cost'])) $player->increaseSalt(-$trade['cost']['salt'] * $quantity);
        if(array_key_exists('fruit', $trade['cost'])) $player->increaseFruit(-$trade['cost']['fruit'] * $quantity);

        $em->flush();

        $trades = $hollowEarthService->getTrades($player);

        $destinationDescription = $destination === LocationEnum::HOME
            ? 'your house'
            : 'your basement'
        ;

        $themOrIt = $quantity === 1 ? 'it' : 'them';

        $responseService->addFlashMessage('You traded for ' . $quantity . '× ' . $item->getName() . '. (Find ' . $themOrIt . ' in ' . $destinationDescription . '.)');

        return $responseService->success($trades);
    }

    /**
     * @Route("/changePet/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function changePet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, HollowEarthService $hollowEarthService
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException();

        if($player->getCurrentAction() !== null || $player->getMovesRemaining() > 0)
            throw new UnprocessableEntityHttpException('Pet cannot be changed at this time.');

        $player->setChosenPet($pet);

        $em->flush();

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }

    /**
     * @Route("/continue", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function continueActing(
        HollowEarthService $hollowEarthService, ResponseService $responseService, EntityManagerInterface $em,
        Request $request, InventoryRepository $inventoryRepository, TransactionService $transactionService,
        Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        if($player->getChosenPet() === null)
            throw new UnprocessableEntityHttpException('You must choose a pet to lead the group.');

        $action = $player->getCurrentAction();

        if($action === null)
        {
            if ($player->getMovesRemaining() > 0)
            {
                $hollowEarthService->advancePlayer($player);

                $em->flush();

                return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
            }
            else
                throw new UnprocessableEntityHttpException('No moves remaining! Roll a die to continue moving.');
        }

        if(!array_key_exists('type', $action))
        {
            $player->setCurrentAction(null);
        }
        else
        {
            switch($action['type'])
            {
                case HollowEarthActionTypeEnum::PAY_ITEM:
                    $this->continueActingPayItem($action, $player, $request->request, $hollowEarthService, $inventoryRepository, $em);
                    break;

                case HollowEarthActionTypeEnum::PAY_MONEY:
                    $this->continueActingPayMoney($action, $player, $request->request, $hollowEarthService, $transactionService);
                    break;

                case HollowEarthActionTypeEnum::PAY_ITEM_AND_MONEY:
                    $this->continueActingPayItemAndMoney($action, $player, $request->request, $hollowEarthService, $inventoryRepository, $em, $transactionService);
                    break;

                case HollowEarthActionTypeEnum::PET_CHALLENGE:
                    $this->continueActingPetChallenge($action, $player, $request->request, $hollowEarthService, $squirrel3);
                    break;

                case HollowEarthActionTypeEnum::CHOOSE_ONE:
                    $this->continueActingChooseOne($action, $player, $request->request, $hollowEarthService);
                    break;

                case HollowEarthActionTypeEnum::MOVE_TO:
                    $hollowEarthService->moveTo($player, $action['id']);
                    break;

                case HollowEarthActionTypeEnum::ONWARD:
                    $player->setCurrentAction(null);
                    break;

                default:
                    throw new \Exception('Unknown action type "' . $action['type'] . '"');
            }
        }

        if($player->getCurrentAction() === null && $player->getMovesRemaining() > 0)
        {
            $hollowEarthService->advancePlayer($player);
        }

        $em->flush();

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }

    private function continueActingChooseOne(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService
    )
    {
        if(!$params->has('choice') || !is_numeric($params->get('choice')))
            throw new UnprocessableEntityHttpException('You must choose one.');

        $choice = (int)$params->get('choice');

        if($choice < 0 || $choice >= count($action['outcomes']))
            throw new UnprocessableEntityHttpException('You must choose one.');

        $chosenOutcome = $action['outcomes'][$choice];

        $hollowEarthService->doImmediateEvent($player, $chosenOutcome);
        $player->setCurrentAction($chosenOutcome);
    }

    private function continueActingPayItem(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService,
        InventoryRepository $inventoryRepository, EntityManagerInterface $em
    )
    {
        if(!$params->has('payUp'))
            throw new UnprocessableEntityHttpException('Will you give up a ' . $action['item'] . ', or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            $itemToPay = $inventoryRepository->findOneToConsume($player->getUser(), $action['item']);

            if(!$itemToPay)
                throw new UnprocessableEntityHttpException('You do not have a ' . $action['item'] . '...');

            $em->remove($itemToPay);

            if(array_key_exists('ifPaid', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifPaid']);
                $player->setCurrentAction($action['ifPaid']);
            }
            else
                $player->setCurrentAction(null);
        }
        else if(array_key_exists('ifNotPaid', $action))
        {
            $hollowEarthService->doImmediateEvent($player, $action['ifNotPaid']);
            $player->setCurrentAction($action['ifNotPaid']);
        }
        else
            $player->setCurrentAction(null);
    }

    private function continueActingPayItemAndMoney(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService,
        InventoryRepository $inventoryRepository, EntityManagerInterface $em, TransactionService $transactionService
    )
    {
        if(!$params->has('payUp'))
            throw new UnprocessableEntityHttpException('Will you give up a ' . $action['item'] . ' and ' . $action['amount'] . '~~m~~, or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            $itemToPay = $inventoryRepository->findOneToConsume($player->getUser(), $action['item']);

            if(!$itemToPay)
                throw new UnprocessableEntityHttpException('You do not have a ' . $action['item'] . '...');

            if($player->getUser()->getMoneys() < $action['amount'])
                throw new UnprocessableEntityHttpException('You don\'t have enough moneys...');

            $em->remove($itemToPay);

            $transactionService->spendMoney($player->getUser(), $action['amount'], 'Spent while exploring the Hollow Earth.');

            if(array_key_exists('ifPaid', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifPaid']);
                $player->setCurrentAction($action['ifPaid']);
            }
            else
                $player->setCurrentAction(null);
        }
        else if(array_key_exists('ifNotPaid', $action))
        {
            $hollowEarthService->doImmediateEvent($player, $action['ifNotPaid']);
            $player->setCurrentAction($action['ifNotPaid']);
        }
        else
            $player->setCurrentAction(null);
    }

    private function continueActingPayMoney(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService,
        TransactionService $transactionService
    )
    {
        if(!$params->has('payUp'))
            throw new UnprocessableEntityHttpException('Will you give up ' . $action['amount'] . '~~m~~, or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            if($player->getUser()->getMoneys() < $action['amount'])
                throw new UnprocessableEntityHttpException('You don\'t have enough moneys...');

            $transactionService->spendMoney($player->getUser(), $action['amount'], 'Spent while exploring the Hollow Earth.');

            if(array_key_exists('ifPaid', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifPaid']);
                $player->setCurrentAction($action['ifPaid']);
            }
            else
                $player->setCurrentAction(null);
        }
        else if(array_key_exists('ifNotPaid', $action))
        {
            $hollowEarthService->doImmediateEvent($player, $action['ifNotPaid']);
            $player->setCurrentAction($action['ifNotPaid']);
        }
        else
            $player->setCurrentAction(null);
    }

    private function continueActingPetChallenge(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService,
        Squirrel3 $squirrel3
    )
    {
        $stats = $action['stats'];
        $score = $action['baseRoll'];

        foreach($stats as $stat)
            $score += $player->getChosenPet()->getComputedSkills()->{'get' . $stat }()->getTotal();

        if($squirrel3->rngNextInt(1, $score) >= $action['requiredRoll'])
        {
            if(array_key_exists('ifSuccess', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifSuccess']);
                $player->setCurrentAction($action['ifSuccess']);
            }
            else
                $player->setCurrentAction(null);
        }
        else
        {
            if(array_key_exists('ifFail', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifFail']);
                $player->setCurrentAction($action['ifFail']);
            }
            else
                $player->setCurrentAction(null);
        }
    }

    /**
     * @Route("/roll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function rollDie(
        ResponseService $responseService, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        HollowEarthService $hollowEarthService, Request $request, CalendarService $calendarService,
        InventoryService $inventoryService, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        if($player->getChosenPet() === null)
            throw new UnprocessableEntityHttpException('You must choose a pet to lead the group.');

        if($player->getCurrentAction() !== null || $player->getMovesRemaining() > 0)
            throw new UnprocessableEntityHttpException('Cannot roll a die at this time...');

        $itemName = $request->request->get('die', '');

        if(!array_key_exists($itemName, HollowEarthService::DICE_ITEMS))
            throw new UnprocessableEntityHttpException('You must specify a die to roll.');

        $inventory = $inventoryRepository->findOneToConsume($user, $itemName);

        if(!$inventory)
            throw new UnprocessableEntityHttpException('You do not have a ' . $itemName . '!');

        $sides = HollowEarthService::DICE_ITEMS[$itemName];
        $moves = $squirrel3->rngNextInt(1, $sides);

        $responseService->addFlashMessage('You rolled a ' . $moves . '!');

        $em->remove($inventory);

        $player->setMovesRemaining($moves);

        $hollowEarthService->advancePlayer($player);

        if($calendarService->isEaster() && $squirrel3->rngNextInt(1, 4) === 1)
        {
            if($squirrel3->rngNextInt(1, 6) === 6)
            {
                if($squirrel3->rngNextInt(1, 12) === 12)
                    $loot = 'Pink Plastic Egg';
                else
                    $loot = 'Yellow Plastic Egg';
            }
            else
                $loot = 'Blue Plastic Egg';

            $inventoryService->receiveItem($loot, $user, $user, $user->getName() . ' spotted this while traveling with ' . $player->getChosenPet()->getName() . ' through the Hollow Earth!', LocationEnum::HOME)
                ->setLockedToOwner($loot !== 'Blue Plastic Egg')
            ;

            if($squirrel3->rngNextInt(1, 10) === 1)
                $responseService->addFlashMessage('(While moving through the Hollow Earth, you spot a ' . $loot . '! But you decide to leave it there... ... nah, I\'m just kidding, of course you scoop the thing up immediately!)');
            else
                $responseService->addFlashMessage('(While moving through the Hollow Earth, you spot a ' . $loot . '!)');
        }

        $em->flush();

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }
}
