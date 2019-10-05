<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\HollowEarthActionTypeEnum;
use App\Enum\HollowEarthRequiredActionEnum;
use App\Enum\SerializationGroupEnum;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class HollowEarthController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getState(ResponseService $responseService)
    {
        $user = $this->getUser();

        if($user->getHollowEarthPlayer() === null)
            throw new AccessDeniedHttpException();

        return $responseService->success($user->getHollowEarthPlayer(), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }

    /**
     * @Route("/changePet/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function changePet(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if($user->getHollowEarthPlayer() === null)
            throw new AccessDeniedHttpException();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException();

        $action = $user->getHollowEarthPlayer()->getAction();

        if($action !== null)
            throw new UnprocessableEntityHttpException('Pet cannot be changed at this time.');

        $user->getHollowEarthPlayer()->setChosenPet($pet);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/continue", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function continueActing(
        HollowEarthService $hollowEarthService, ResponseService $responseService, EntityManagerInterface $em,
        Request $request
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        $action = $player->getAction();

        if($action === null)
        {
            if ($player->getMovesRemaining() > 0)
            {
                $hollowEarthService->advancePlayer($player);

                $em->flush();

                return $responseService->success($player, [SerializationGroupEnum::HOLLOW_EARTH]);
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
                    if(!$request->request->has('payUp'))
                        throw new UnprocessableEntityHttpException('Will you give up a ' . $action['item'] . ', or no?');

                    $payUp = $request->request->getBoolean('payUp');

                    if($payUp)
                    {
                        throw new \Exception('Not yet implemented! Sorry :(');
                        // TODO: if user has the item:
                        //   spend the item
                        //   if the action has an ifPaid, execute that action, else $player->setCurrentAction(null);
                        //   break;
                    }

                    throw new \Exception('Not yet implemented! Sorry :(');
                    // TODO: else
                    //   if the action has an ifNotPaid, execute that action, else $player->setCurrentAction(null);

                    break;

                case HollowEarthActionTypeEnum::PAY_MONEY:
                    if(!$request->request->has('payUp'))
                        throw new UnprocessableEntityHttpException('Will you give up ' . $action['amount'] . '~~m~~, or no?');

                    $payUp = $request->request->getBoolean('payUp');

                    if($payUp)
                    {
                        if($user->getMoneys() < $action['amount'])
                            throw new UnprocessableEntityHttpException('You don\'t  have enough moneys...');

                        $user->increaseMoneys(-$action['amount']);

                        if($action['ifPaid'])
                        {
                            $hollowEarthService->doImmediateAction($player, $action['ifPaid']);
                            $player->setCurrentAction($action['ifPaid']);
                        }
                        else
                            $player->setCurrentAction(null);

                        break;
                    }

                    if($action['ifNotPaid'])
                    {
                        $hollowEarthService->doImmediateAction($player, $action['ifNotPaid']);
                        $player->setCurrentAction($action['ifNotPaid']);
                    }
                    else
                        $player->setCurrentAction(null);

                    break;

                case HollowEarthActionTypeEnum::PET_CHALLENGE:
                    throw new \Exception('Not yet implemented! Sorry :(');
                    // TODO

                case HollowEarthActionTypeEnum::MOVE_TO:
                    $player->setCurrentAction(null);
                    break;
            }
        }

        if($player->getCurrentAction() === null && $player->getMovesRemaining() > 0)
        {
            $hollowEarthService->advancePlayer($player);
        }

        $em->flush();

        return $responseService->success($player, [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }

    /**
     * @Route("/rollDie/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function rollDie(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        HollowEarthService $hollowEarthService
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException();

        if($player->getAction() !== null || $player->getMovesRemaining() > 0)
            throw new UnprocessableEntityHttpException('Cannot roll a die at this time...');

        $itemName = $inventory->getItem()->getName();

        switch($itemName)
        {
            case 'Glowing Four-sided Die': $moves = mt_rand(1, 4); break;
            case 'Glowing Six-sided Die': $moves = mt_rand(1, 6); break;
            case 'Glowing Eight-sided Die': $moves = mt_rand(1, 8); break;
            default: throw new UnprocessableEntityHttpException('Selected item is not a die!');
        }

        $responseService->addActivityLog((new PetActivityLog())->setEntry('You rolled a ' . $moves . '!'));

        $em->remove($inventory);

        $player->setMovesRemaining($moves);

        $hollowEarthService->advancePlayer($player);

        $em->flush();

        return $responseService->success($player, [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }
}