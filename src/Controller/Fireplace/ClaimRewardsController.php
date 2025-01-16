<?php
namespace App\Controller\Fireplace;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\PetBadgeHelpers;
use App\Functions\PlayerLogFactory;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/fireplace")]
class ClaimRewardsController extends AbstractController
{
    #[Route("/claimRewards", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function claimRewards(
        InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $fireplace = $user->getFireplace();

        if(!$fireplace->getHasReward())
            throw new PSPInvalidOperationException('There\'s nothing unusual in the fireplace right now... (That\'s odd. Reload and try again?)');

        $numItems = min(3, (int)($fireplace->getPoints() / (8 * 60)));

        $rewardLevelBonus = min(7, (int)($fireplace->getCurrentStreak() / (24 * 60)));

        $possibleRewards = [
            'Quintessence',
            'Naner Pancakes',
            'Burnt Log',
            'Burnt Log',
            'Poker',
            'Hot Dog',
            'Glowing Four-sided Die',
            'Glowing Six-sided Die',

            'Fairy Ring', // 1 day
            'Iron Ore', // 2 days
            'Glowing Eight-sided Die', // 3 days
            'Bag of Beans', // 4 days
            'Box of Ores', // 5 days
            'Magic Beans', // 6 days
            'House Fairy', // 7 days
        ];

        $itemsReceived = [];

        for($i = 0; $i < $numItems; $i++)
        {
            $itemName = $possibleRewards[$squirrel3->rngNextInt(0, $squirrel3->rngNextInt(7, 7 + $rewardLevelBonus))];

            if($itemName === 'House Fairy')
            {
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Oh! Hello!)', LocationEnum::HOME);

                // triggers Hyssop letter #3
                $oldValue = UserQuestRepository::findOrCreate($em, $user, 'Can Receive Letters from Fairies', 0);
                if($oldValue->getValue() === 2)
                    $oldValue->setValue(3);
            }
            else if($itemName === 'Burnt Log')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Nothing surprising there.)', LocationEnum::HOME);
            else if($itemName === 'Poker')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Oops! How\'d that get left in there!)', LocationEnum::HOME);
            else if($itemName === 'Naner Pancakes' || $itemName === 'Hot Dog')
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Whew! didn\'t burn!)', LocationEnum::HOME);
            else if($squirrel3->rngNextInt(1, 4) === 1)
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Did someone put that in there? It seems like someone put that in there.)', LocationEnum::HOME);
            else
                $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this in their fireplace. (Is that... normal?)', LocationEnum::HOME);

            $itemsReceived[] = $itemName;
        }

        if($numItems === 3)
            $fireplace->clearPoints();
        else
            $fireplace->spendPoints($numItems * 8 * 60);

        $message = ($fireplace->getHeat() >= 2 * 60 && $squirrel3->rngNextInt(1, 3) === 1)
            ? 'You reach inside the Fireplace while the fire is still burning, just like a totally normal person would do, and pull out ' . ArrayFunctions::list_nice($itemsReceived) . '!'
            : 'You reach inside the Fireplace, and pull out ' . ArrayFunctions::list_nice($itemsReceived) . '!';

        $responseService->addFlashMessage($message);

        PlayerLogFactory::create($em, $user, $message, [ 'Fireplace' ]);

        if($numItems > 0 && $fireplace->getGnomePoints() >= 24)
        {
            $fireplace->spendGnomePoints(24);

            $inventoryService->receiveItem('Gnome\'s Favor', $user, $user, $user->getName() . ' was given this by a Fireplace Gnome!', LocationEnum::HOME);

            $gnomishMessage = $squirrel3->rngNextFromArray([
                'A gnome stumbles out of the fireplace and thanks you for your gifts before falling into a gap in the hearth\'s brickwork.',
                'A gnome pokes its head out of a gap in the hearth\'s brickwork, teeters for a bit, then gives you a thumbs up before vanishing in the darkness...',
                'A gnome wobbles past you, into the fireplace, and trips into a gap in the hearth\'s brickwork, vanishing. You hear a fading "yeaaah, ' . GrammarFunctions::stretchWord($user->getName()) . '!" echo from the fireplace.'
            ]);

            $responseService->addFlashMessage($gnomishMessage . ' (You received a Gnome\'s Favor!)');

            PlayerLogFactory::create($em, $user, $gnomishMessage . ' (You received a Gnome\'s Favor!)', [ 'Fireplace' ]);

            if($fireplace->getHelper())
            {
                PetBadgeHelpers::awardBadgeAndLog($em, $fireplace->getHelper(), PetBadgeEnum::WAS_A_CHIMNEY_SWEEP, ActivityHelpers::PetName($fireplace->getHelper()) . ' helped clean up, afterwards.');
            }
        }

        $em->flush();

        return $responseService->success($fireplace, [
            SerializationGroupEnum::MY_FIREPLACE,
            SerializationGroupEnum::HELPER_PET,
        ]);
    }
}
