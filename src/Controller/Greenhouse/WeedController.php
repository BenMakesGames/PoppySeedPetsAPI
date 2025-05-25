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


namespace App\Controller\Greenhouse;

use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetBadgeEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\CalendarFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\PlayerLogFactory;
use App\Functions\UserQuestRepository;
use App\Model\PetChanges;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/greenhouse")]
class WeedController
{
    #[Route("/weed", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function weedPlants(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService,
        IRandom $rng, Clock $clock,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $greenhouse = $user->getGreenhouse();

        if(!$greenhouse)
            throw new PSPNotFoundException('You don\'t have a Greenhouse plot.');

        $weeds = UserQuestRepository::findOrCreate($em, $user, 'Greenhouse Weeds', (new \DateTimeImmutable())->modify('-1 minutes')->format('Y-m-d H:i:s'));

        $weedTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $weeds->getValue());

        if($weedTime > new \DateTimeImmutable())
            throw new PSPInvalidOperationException('Your garden\'s doin\' just fine right now, weed-wise.');

        $weeds->setValue((new \DateTimeImmutable())->modify('+18 hours')->format('Y-m-d H:i:s'));

        if($rng->rngNextInt(1, 4) === 1)
            $itemName = $rng->rngNextFromArray([ 'Fluff', 'Red Clover', 'Talon', 'Feathers' ]);
        else
            $itemName = $rng->rngNextFromArray([ 'Dandelion', 'Crooked Stick', 'Crooked Stick' ]);

        $foundItem = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this while weeding their Greenhouse.', LocationEnum::Home);
        $foundItem2 = null;

        if($greenhouse->isHasFishStatue())
        {
            $possibleItem2s = CalendarFunctions::isSaintPatricksDay($clock->now)
                ? [ '1-leaf Clover', '2-leaf Clover' ]
                : [
                    'Algae',
                    'Scales',
                    'Freshly-squeezed Fish Oil', // greenhouse fishin' badge (below) is awarded for finding this
                    'Silica Grounds'
                ]
            ;

            $foundItem2 = $inventoryService->receiveItem($rng->rngNextFromArray($possibleItem2s), $user, $user, $user->getName() . ' found this while cleaning their Fish Statue.', LocationEnum::Home);

            $message = 'You found ' . $foundItem->getItem()->getNameWithArticle() . ' while cleaning up, plus ' . $foundItem2->getItem()->getNameWithArticle() . ' near the Fish Statue!';

        }
        else
        {
            $message = 'You found ' . $foundItem->getItem()->getNameWithArticle() .' while cleaning up!';
        }

        if($greenhouse->getHelper())
        {
            $helper = $greenhouse->getHelper();
            $petWithSkills = $helper->getComputedSkills();
            $skill = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal();

            $hasWaterPlots = $greenhouse->getMaxWaterPlants() > 0;
            $hasDarkPlots = $greenhouse->getMaxDarkPlants() > 0;
            $isRaining = WeatherService::getWeather(new \DateTimeImmutable(), null)->getRainfall() > 0;

            if(CalendarFunctions::isApricotFestival($clock->now))
                $basicItems = [ 'Apricot', 'Blueberries', 'Line of Ants' ];
            else
                $basicItems = [ 'Egg', 'Blackberries', 'Blueberries', 'Line of Ants' ];

            $slightlyCoolerItems = [ 'Narcissus', 'Plastic', 'Paper', 'Pepino Dulce' ];

            if($hasDarkPlots)
            {
                $basicItems[] = $rng->rngNextFromArray([ 'Toadstool', 'Chanterelle' ]);
            }

            if($hasWaterPlots)
            {
                $basicItems[] = 'Scales'; // greenhouse fishin' badge (below) is awarded for finding this
            }

            if($isRaining)
            {
                $slightlyCoolerItems[] = 'Worms';
            }

            $extraItem = PetAssistantService::getExtraItem(
                $rng,
                $skill,
                $basicItems,
                $slightlyCoolerItems,
                [ 'Coconut', 'Dark Matter', 'Filthy Cloth' ],
                [ 'Mango', 'Gypsum', 'Really Big Leaf', 'White Feathers' ]
            );

            $extraItemObject = ItemRepository::findOneByName($em, $extraItem);

            $surprisingItems = [ 'Coconut', 'Mango' ];
            $litterItems = [ 'Plastic', 'Paper', 'Filthy Cloth' ];

            if(in_array($extraItem, $surprisingItems))
                $extraDetail = '! (As a weed?! Weird!)';
            else if(in_array($extraItem, $litterItems))
                $extraDetail = '! (Weeds are bad enough; what\'s this litter doing here?!)';
            else
                $extraDetail = '.';

            $changes = new PetChanges($helper);
            $activityLogEntry = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . ' weed their Greenhouse, and found ' . $extraItemObject->getNameWithArticle() . $extraDetail);

            $bonusFlower = null;

            if($helper->hasMerit(MeritEnum::GREEN_THUMB))
            {
                $possibleFlowers = [
                    'Agrimony',
                    'Bird\'s-foot Trefoil',
                    'Coriander Flower',
                    'Green Carnation',
                    'Iris',
                    'Narcissus',
                    'Purple Violet',
                    'Red Clover',
                    'Rice Flower',
                    'Viscaria',
                    'Wheat Flower',
                    'Witch-hazel',
                    'Merigold',
                ];

                if($hasWaterPlots)
                    $possibleFlowers[] = 'Lotus Flower';

                $bonusFlower = $rng->rngNextFromArray($possibleFlowers);

                $activityLogEntry->setEntry($activityLogEntry->getEntry() . ' ... oh! And a ' . $bonusFlower . '!');
            }

            $inventoryService->petCollectsItem($extraItemObject, $helper, $helper->getName() . ' found this while weeding the Greenhouse with ' . $user->getName() . $extraDetail, $activityLogEntry);

            if($bonusFlower)
                $inventoryService->petCollectsItem($bonusFlower, $helper, $helper->getName() . ' found this while weeding the Greenhouse with ' . $user->getName() . '.', $activityLogEntry);

            $activityLogEntry
                ->addInterestingness(PetActivityLogInterestingness::PlayerActionResponse)
                ->setChanges($changes->compare($helper))
                ->addTags(PetActivityLogTagHelpers::findByNames($em, [ 'Add-on Assistance', 'Greenhouse' ]))
            ;

            $foundItem2Name = $foundItem2?->getItem()?->getName();

            if($extraItem === 'Scales' || $foundItem2Name === 'Scales' || $foundItem2Name === 'Freshly-squeezed Fish Oil')
                PetBadgeHelpers::awardBadge($em, $helper, PetBadgeEnum::GREENHOUSE_FISHER, $activityLogEntry);
        }

        $message .= ' ' . $rng->rngNextFromArray([ 'Noice!', 'Yoink!', '👍', '👌', 'Neat-o!', 'Okey dokey!' ]);

        PlayerLogFactory::create($em, $user, $message, [ 'Greenhouse' ]);

        $em->flush();

        return $responseService->success($message);
    }
}
