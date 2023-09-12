<?php
namespace App\Controller\HollowEarth;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\CalendarService;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class RollDieController extends AbstractController
{
    /**
     * @Route("/roll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function rollDie(
        ResponseService $responseService, EntityManagerInterface $em, InventoryRepository $inventoryRepository,
        HollowEarthService $hollowEarthService, Request $request, InventoryService $inventoryService,
        Squirrel3 $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();
        $now = new \DateTimeImmutable();

        if($player === null)
            throw new PSPInvalidOperationException('You gotta\' visit the Hollow Earth page at least once before taking this kind of action.');

        if($player->getChosenPet() === null)
            throw new PSPInvalidOperationException('You must choose a pet to lead the group.');

        if($player->getCurrentAction() !== null || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('Cannot roll a die at this time...');

        $itemName = $request->request->get('die', '');

        if(!array_key_exists($itemName, HollowEarthService::DICE_ITEMS))
            throw new PSPFormValidationException('You must specify a die to roll.');

        $inventory = $inventoryRepository->findOneToConsume($user, $itemName);

        if(!$inventory)
            throw new PSPNotFoundException('You do not have a ' . $itemName . '!');

        $sides = HollowEarthService::DICE_ITEMS[$itemName];
        $moves = $squirrel3->rngNextInt(1, $sides);

        $responseService->addFlashMessage('You rolled a ' . $moves . '!');

        $em->remove($inventory);

        $player->setMovesRemaining($moves);

        $hollowEarthService->advancePlayer($player);

        if(CalendarService::isEaster($now) && $squirrel3->rngNextInt(1, 4) === 1)
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
