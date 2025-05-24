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


namespace App\Controller\Halloween;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\CalendarFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PlayerLogFactory;
use App\Model\FoodWithSpice;
use App\Service\Clock;
use App\Service\FieldGuideService;
use App\Service\Holidays\HalloweenService;
use App\Service\IRandom;
use App\Service\PetActivity\EatingService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/halloween")]
class GiveCandyToTrickOrTreaterController
{
    #[Route("/trickOrTreater/giveCandy", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function giveCandy(
        ResponseService $responseService, EntityManagerInterface $em, HalloweenService $halloweenService,
        Request $request, Clock $clock, IRandom $rng, FieldGuideService $fieldGuideService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!CalendarFunctions::isHalloween($clock->now))
            throw new PSPInvalidOperationException('It isn\'t Halloween!');

        $inventoryId = $request->request->getInt('candy');

        if($inventoryId < 1)
            throw new PSPInvalidOperationException('You must select a candy to give!');

        $candy = $em->getRepository(Inventory::class)->findOneBy([
            'id' => $inventoryId,
            'owner' => $user->getId(),
            'location' => LocationEnum::Home
        ]);

        if(!$candy)
            throw new PSPNotFoundException('The selected candy could not be found... reload and try again?');

        if(!$candy->getItem()->getFood())
            throw new PSPInvalidOperationException($candy->getItem()->getName() . ' isn\'t even edible!');

        if(!$candy->getItem()->getFood()->getIsCandy())
            throw new PSPInvalidOperationException($candy->getItem()->getName() . ' isn\'t quiiiiiiite a candy.');

        $toGivingTree = $request->request->getBoolean('toGivingTree', false);

        $nextTrickOrTreater = $halloweenService->getNextTrickOrTreater($user);

        if((new \DateTimeImmutable())->format('Y-m-d H:i:s') < $nextTrickOrTreater->getValue())
            return $responseService->success([ 'trickOrTreater' => null, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue() ]);

        $trickOrTreater = $halloweenService->getTrickOrTreater($user);

        $halloweenService->resetTrickOrTreater($user);

        if($trickOrTreater === null)
        {
            $em->flush();

            throw new PSPNotFoundException('No one else\'s pets are trick-or-treating right now! (Not many people must be playing :| TELL YOUR FRIENDS TO SIGN IN AND DRESS UP THEIR PETS!');
        }

        if($toGivingTree)
        {
            $givingTree = $em->getRepository(User::class)->findOneBy([ 'email' => 'giving-tree@poppyseedpets.com' ]);

            $candy->changeOwner($givingTree, $user->getName() . ' gave this to the Giving Tree during Halloween!', $em);
        }
        else
        {
            $candy->changeOwner($trickOrTreater->getOwner(), $trickOrTreater->getName() . ' received this trick-or-treating at ' . $user->getName() . '\'s house!', $em);

            $logMessage = $trickOrTreater->getName() . ' went trick-or-treating at ' . $user->getName() . '\'s house, and received ' . $candy->getItem()->getNameWithArticle() . '!';

            $favoriteFlavorStrength = EatingService::getFavoriteFlavorStrength($trickOrTreater, new FoodWithSpice($candy->getItem(), null));

            if($favoriteFlavorStrength > 0)
                $logMessage .= ' (' . $rng->rngNextFromArray([ 'Just what they wanted!', 'Ah! The good stuff!', 'One of their favorites!' ]) . ')';

            PetActivityLogFactory::createUnreadLog($em, $trickOrTreater, $logMessage)
                ->addInterestingness(PetActivityLogInterestingness::HolidayOrSpecialEvent)
                ->setIcon('ui/halloween')
                ->addTags(PetActivityLogTagHelpers::findByNames($em, [ 'Special Event', 'Halloween' ]))
            ;

            PlayerLogFactory::create(
                $em,
                $user,
                'You gave ' . $candy->getFullItemName() . ' to ' . GrammarFunctions::indefiniteArticle($trickOrTreater->getSpecies()->getName()) . ' ' . $trickOrTreater->getSpecies()->getName() . ' dressed as ' . $trickOrTreater->getCostume() . '!',
                [ PetActivityLogTagEnum::Special_Event, PetActivityLogTagEnum::Halloween ]
            );
        }

        $reward = $halloweenService->countCandyGiven($user, $trickOrTreater, $toGivingTree);

        if($toGivingTree)
        {
            if($reward)
            {
                $responseService->addFlashMessage('The pet moves on to the next house. Also, while at the Giving Tree, you spot ' . $reward->getItem()->getNameWithArticle() . ' with your name on it! Whoa!');
            }
            else
            {
                $responseService->addFlashMessage('The pet moves on to the next house.');
            }
        }
        else
        {
            if($reward)
            {
                $responseService->addFlashMessage('Before leaving for the next house, ' . $trickOrTreater->getName() . ' hands you ' . $reward->getItem()->getNameWithArticle() . '!');
            }
            else
            {
                $responseService->addFlashMessage($trickOrTreater->getName() . ' happily takes the candy and heads off to the next house.');
            }
        }

        $fieldGuideService->maybeUnlock($user, 'Trick-or-treating', 'After giving candy to trick-or-treaters, %user:' . $user->getId() . '.Name% found this just outside their front door...');

        $em->flush();

        return $responseService->success([ 'trickOrTreater' => null, 'nextTrickOrTreater' => $nextTrickOrTreater->getValue(), 'candy' => [] ]);
    }
}
