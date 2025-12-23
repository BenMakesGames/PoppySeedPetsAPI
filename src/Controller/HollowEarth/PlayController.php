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

namespace App\Controller\HollowEarth;

use App\Entity\HollowEarthPlayer;
use App\Entity\HollowEarthPlayerTile;
use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\InventoryHelpers;
use App\Service\HollowEarthService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/hollowEarth")]
class PlayController
{
    #[Route("/continue", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function continueActing(
        HollowEarthService $hollowEarthService, ResponseService $responseService, EntityManagerInterface $em,
        Request $request, TransactionService $transactionService, IRandom $rng, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new PSPNotUnlockedException('Hollow Earth');

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
                case HollowEarthActionTypeEnum::PayItem:
                    $this->continueActingPayItem($action, $player, $request->request, $hollowEarthService, $em);
                    break;

                case HollowEarthActionTypeEnum::PayMoneys:
                    $this->continueActingPayMoney($action, $player, $request->request, $hollowEarthService, $transactionService);
                    break;

                case HollowEarthActionTypeEnum::PayItemAndMoneys:
                    $this->continueActingPayItemAndMoney($action, $player, $request->request, $hollowEarthService, $em, $transactionService);
                    break;

                case HollowEarthActionTypeEnum::PetChallenge:
                    $this->continueActingPetChallenge($action, $player, $request->request, $hollowEarthService, $rng, $em);
                    break;

                case HollowEarthActionTypeEnum::ChooseOne:
                    $this->continueActingChooseOne($action, $player, $request->request, $hollowEarthService);
                    break;

                case HollowEarthActionTypeEnum::MoveTo:
                    $hollowEarthService->moveTo($player, $action['id']);
                    break;

                case HollowEarthActionTypeEnum::Onward:
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
    ): void
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
        EntityManagerInterface $em
    ): void
    {
        if(!$params->has('payUp'))
            throw new PSPFormValidationException('Will you give up a ' . $action['item'] . ', or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            $itemToPay = InventoryHelpers::findOneToConsume($em, $player->getUser(), $action['item']);

            if(!$itemToPay)
                throw new PSPNotFoundException('You do not have a ' . $action['item'] . '...');

            $em->remove($itemToPay);

            if(!array_key_exists('ifPaid', $action))
                throw new \Exception('No paid action defined for this challenge.');

            $hollowEarthService->doImmediateEvent($player, $action['ifPaid']);
            $player->setCurrentAction($action['ifPaid']);
        }
        else
        {
            if(array_key_exists('ifNotPaid', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifNotPaid']);
                $player->setCurrentAction($action['ifNotPaid']);
            }
            else
                $player->setCurrentAction(null);
        }
    }

    private function continueActingPayItemAndMoney(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService,
        EntityManagerInterface $em, TransactionService $transactionService
    ): void
    {
        if(!$params->has('payUp'))
            throw new PSPFormValidationException('Will you give up a ' . $action['item'] . ' and ' . $action['amount'] . '~~m~~, or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            $itemToPay = InventoryHelpers::findOneToConsume($em, $player->getUser(), $action['item']);

            if(!$itemToPay)
                throw new PSPNotFoundException('You do not have a ' . $action['item'] . '...');

            if($player->getUser()->getMoneys() < $action['amount'])
                throw new PSPNotEnoughCurrencyException($action['amount'] . '~~m~~', $player->getUser()->getMoneys() . '~~m~~');

            $em->remove($itemToPay);

            $transactionService->spendMoney($player->getUser(), $action['amount'], 'Spent while exploring the Hollow Earth.');

            if(!array_key_exists('ifPaid', $action))
                throw new \Exception('No paid action defined for this challenge.');

            $hollowEarthService->doImmediateEvent($player, $action['ifPaid']);
            $player->setCurrentAction($action['ifPaid']);
        }
        else
        {
            if(array_key_exists('ifNotPaid', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifNotPaid']);
                $player->setCurrentAction($action['ifNotPaid']);
            }
            else
                $player->setCurrentAction(null);
        }
    }

    private function continueActingPayMoney(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService,
        TransactionService $transactionService
    ): void
    {
        if(!$params->has('payUp'))
            throw new PSPFormValidationException('Will you give up ' . $action['amount'] . '~~m~~, or no?');

        $payUp = $params->getBoolean('payUp');

        if($payUp)
        {
            if($player->getUser()->getMoneys() < $action['amount'])
                throw new PSPNotEnoughCurrencyException($action['amount'] . '~~m~~', $player->getUser()->getMoneys() . '~~m~~');

            $transactionService->spendMoney($player->getUser(), $action['amount'], 'Spent while exploring the Hollow Earth.');

            if(!array_key_exists('ifPaid', $action))
                throw new \Exception('No paid action defined for this challenge.');

            $hollowEarthService->doImmediateEvent($player, $action['ifPaid']);
            $player->setCurrentAction($action['ifPaid']);
        }
        else
        {
            if(array_key_exists('ifNotPaid', $action))
            {
                $hollowEarthService->doImmediateEvent($player, $action['ifNotPaid']);
                $player->setCurrentAction($action['ifNotPaid']);
            }
            else
                $player->setCurrentAction(null);
        }
    }

    private function continueActingPetChallenge(
        array $action, HollowEarthPlayer $player, ParameterBag $params, HollowEarthService $hollowEarthService,
        IRandom $rng, EntityManagerInterface $em
    ): void
    {
        if(!array_key_exists('ifSuccess', $action))
        {
            /** @var HollowEarthPlayerTile|null $currentTile */
            $currentTile = $em->getRepository(HollowEarthPlayerTile::class)->findOneBy([
                'player' => $player,
                'tile' => $player->getCurrentTile(),
            ]);

            if(!$currentTile)
                throw new \Exception("No success action defined for this challenge... and the player has no current tile?!");
            else if(!$currentTile->getCard())
                throw new \Exception("No success action defined for this challenge... and the player's tile has no card?!");
            else
                throw new \Exception("No success action defined for this challenge. Current tile card name: {$currentTile->getCard()->getName()}");
        }

        // old tiles refer to the "umbra" skill, but that is no longer a skill; it was renamed to arcana, so:
        $stats = array_map(fn($stat) => $stat === 'umbra' ? 'arcana' : $stat, $action['stats']);
        $score = $action['baseRoll'];

        foreach($stats as $stat)
            $score += $player->getChosenPet()->getComputedSkills()->{'get' . $stat }()->getTotal();

        if($rng->rngNextInt(1, $score) >= $action['requiredRoll'])
        {
            $hollowEarthService->doImmediateEvent($player, $action['ifSuccess']);
            $player->setCurrentAction($action['ifSuccess']);
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
