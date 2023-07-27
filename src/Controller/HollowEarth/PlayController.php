<?php
namespace App\Controller\HollowEarth;

use App\Entity\HollowEarthPlayer;
use App\Entity\User;
use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\InventoryRepository;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class PlayController extends AbstractController
{
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
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new PSPNotUnlockedException('Portal');

        if($player->getChosenPet() === null)
            throw new PSPInvalidOperationException('You must choose a pet to lead the group.');

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
                throw new PSPInvalidOperationException('No moves remaining! Roll a die to continue moving.');
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
            throw new PSPFormValidationException('You must choose one.');

        $choice = (int)$params->get('choice');

        if($choice < 0 || $choice >= count($action['outcomes']))
            throw new PSPFormValidationException('You must choose one.');

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
            throw new PSPFormValidationException('Will you give up a ' . $action['item'] . ', or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            $itemToPay = $inventoryRepository->findOneToConsume($player->getUser(), $action['item']);

            if(!$itemToPay)
                throw new PSPNotFoundException('You do not have a ' . $action['item'] . '...');

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
            throw new PSPFormValidationException('Will you give up a ' . $action['item'] . ' and ' . $action['amount'] . '~~m~~, or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            $itemToPay = $inventoryRepository->findOneToConsume($player->getUser(), $action['item']);

            if(!$itemToPay)
                throw new PSPNotFoundException('You do not have a ' . $action['item'] . '...');

            if($player->getUser()->getMoneys() < $action['amount'])
                throw new PSPNotEnoughCurrencyException($action['amount'] . '~~m~~', $player->getUser()->getMoneys() . '~~m~~');

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
            throw new PSPFormValidationException('Will you give up ' . $action['amount'] . '~~m~~, or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            if($player->getUser()->getMoneys() < $action['amount'])
                throw new PSPNotEnoughCurrencyException($action['amount'] . '~~m~~', $player->getUser()->getMoneys() . '~~m~~');

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
}
