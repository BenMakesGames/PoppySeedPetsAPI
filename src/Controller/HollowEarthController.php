<?php
namespace App\Controller;

use App\Entity\HollowEarthPlayer;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\HollowEarthRequiredActionEnum;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
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
        Request $request, InventoryRepository $inventoryRepository, TransactionService $transactionService
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

                case HollowEarthActionTypeEnum::PET_CHALLENGE:
                    $this->continueActingPetChallenge($action, $player, $request->request, $hollowEarthService);
                    break;

                case HollowEarthActionTypeEnum::CHOOSE_ONE:
                    $this->continueActingChooseOne($action, $player, $request->request, $hollowEarthService);
                    break;

                case HollowEarthActionTypeEnum::MOVE_TO:
                    $hollowEarthService->moveTo($player, $action['id']);
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
