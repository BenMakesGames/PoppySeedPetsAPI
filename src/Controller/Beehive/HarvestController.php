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


namespace App\Controller\Beehive;

use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetBadgeEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\SpiceRepository;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/beehive")]
class HarvestController
{
    #[Route("/harvest", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function harvest(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive) || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $beehive = $user->getBeehive();
        $itemNames = [];

        if($beehive->getRoyalJellyPercent() >= 1)
        {
            $beehive->setRoyalJellyProgress(0);

            $inventoryService->receiveItem('Royal Jelly', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::Home);

            $itemNames[] = 'Royal Jelly';
        }

        if($beehive->getHoneycombPercent() >= 1)
        {
            $beehive->setHoneycombProgress(0);

            $inventoryService->receiveItem('Honeycomb', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::Home);

            $itemNames[] = 'Honeycomb';
        }

        if($beehive->getMiscPercent() >= 1)
        {
            $beehive->setMiscProgress(0);

            $possibleItems = [
                'Crooked Stick', 'Fluff', 'Yellow Dye', 'Glue', 'Sugar', 'Sugar', 'Sugar', 'Antenna',
            ];

            $item = $rng->rngNextFromArray($possibleItems);

            $newItems = [
                $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::Home)
            ];

            if($beehive->getHelper())
            {
                $helper = $beehive->getHelper();
                $petWithSkills = $helper->getComputedSkills();

                $changes = new PetChanges($helper);

                if($helper->hasMerit(MeritEnum::GREEN_THUMB))
                {
                    $gathering = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();

                    $extraItem1 = PetAssistantService::getExtraItem($rng, $gathering,
                        [ 'Tea Leaves', 'Blueberries', 'Blackberries', 'Grandparoot', 'Orange', 'Red' ],
                        [ 'Onion', 'Paper', 'Naner', /* Naner is used for badge, below */ 'Iron Ore' ],
                        [ 'Gypsum', 'Mixed Nuts', 'Apricot', 'Silver Ore', ],
                        [ 'Gold Ore', 'Liquid-hot Magma' ]
                    );

                    $extraItem2 = PetAssistantService::getExtraItem($rng, $gathering,
                        [ 'Agrimony', 'Blueberries', 'Blackberries', 'Orange', 'Red' ],
                        [ 'Onion', 'Tomato', 'Naner', /* Naner is used for badge, below */ 'Sunflower' ],
                        [ 'Mint', 'Mixed Nuts', 'Apricot', 'Melowatern', ],
                        [ 'Goodberries', 'Iris' ]
                    );

                    $activityLog = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . '\'s bees while they were out gathering, and collected ' . $extraItem1 . ' AND ' . $extraItem2 . '.');

                    $inventoryService->petCollectsItem($extraItem1, $helper, $helper->getName() . ' helped ' . $user->getName() . '\'s bees gathered this.', $activityLog);
                    $inventoryService->petCollectsItem($extraItem2, $helper, $helper->getName() . ' helped ' . $user->getName() . '\'s bees gathered this.', $activityLog);

                    if($extraItem1 === 'Naner' || $extraItem2 === 'Naner')
                        PetBadgeHelpers::awardBadge($em, $helper, PetBadgeEnum::BeeNana, $activityLog);
                }
                else
                {
                    $gathering = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();
                    $hunting = $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal();

                    $total = $gathering + $hunting;

                    if($total < 2)
                        $doGatherAction = $rng->rngNextBool();
                    else
                        $doGatherAction = $rng->rngNextInt(1, $total) <= $gathering;

                    if($doGatherAction)
                    {
                        $extraItem = PetAssistantService::getExtraItem($rng, $gathering,
                            [ 'Tea Leaves', 'Blueberries', 'Blackberries', 'Grandparoot', 'Orange', 'Red' ],
                            [ 'Onion', 'Paper', 'Naner', /* Naner is used for badge, below */ 'Iron Ore' ],
                            [ 'Gypsum', 'Mixed Nuts', 'Apricot', 'Silver Ore', ],
                            [ 'Gold Ore', 'Liquid-hot Magma' ],
                        );

                        $verb = 'gather';
                    }
                    else
                    {
                        $extraItem = PetAssistantService::getExtraItem($rng, $hunting,
                            [ 'Scales', 'Feathers', 'Egg' ],
                            [ 'Toadstool', 'Talon', 'Onion' ],
                            [ 'Toad Legs', 'Jar of Fireflies' ],
                            [ 'Silver Bar', 'Gold Bar', 'Quintessence' ],
                        );

                        $verb = 'hunt';
                    }

                    $activityLog = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . '\'s bees while they were out ' . $verb . 'ing, and collected ' . $extraItem . '.');

                    $inventoryService->petCollectsItem($extraItem, $helper, $helper->getName() . ' helped ' . $user->getName() . '\'s bees ' . $verb . ' this.', $activityLog);

                    if($extraItem === 'Naner')
                        PetBadgeHelpers::awardBadge($em, $helper, PetBadgeEnum::BeeNana, $activityLog);
                }

                $activityLog
                    ->addInterestingness(PetActivityLogInterestingness::PlayerActionResponse)
                    ->setChanges($changes->compare($helper))
                    ->addTags(PetActivityLogTagHelpers::findByNames($em, [ 'Add-on Assistance', 'Beehive' ]))
                ;
            }

            foreach($newItems as $newItem)
            {
                if($newItem->getItem()->getName() === 'Crooked Stick' || $newItem->getItem()->getFood())
                {
                    if($rng->rngNextInt(1, 20) === 1)
                        $newItem->setSpice(SpiceRepository::findOneByName($em, 'of Queens'));
                    else
                        $newItem->setSpice(SpiceRepository::findOneByName($em, 'Anthophilan'));
                }

                $itemNames[] = $newItem->getFullItemName();
            }
        }

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        $responseService->addFlashMessage('You received ' . ArrayFunctions::list_nice($itemNames) . '.');

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
