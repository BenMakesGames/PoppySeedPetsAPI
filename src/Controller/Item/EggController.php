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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\PetSpecies;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSpeciesName;
use App\Functions\MeritRepository;
use App\Functions\PetColorFunctions;
use App\Functions\PetRepository;
use App\Functions\PetSpeciesRepository;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/egg")]
class EggController
{
    #[Route("/jellingPolyp/{inventory}/hatch", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function hatchPolyp(
        Inventory $inventory, ResponseService $responseService, IRandom $rng, EntityManagerInterface $em,
        PetFactory $petFactory, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'egg/jellingPolyp/#/hatch');

        $jelling = PetSpeciesRepository::findOneByName($em,PetSpeciesName::SagaJelling);

        if(!$jelling)
            throw new \Exception('The species "Sága Jelling" does not exist! :| Make Ben fix this!');

        $location = $inventory->getLocation();

        if($location !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('You can\'t hatch it here! Take it to your house, quick!');

        $message = "A jellyfish detaches itself from the polyp, ";

        $em->remove($inventory);

        $jellingName = $rng->rngNextFromArray([
            'Epistêmê',
            'Gyaan',
            'Wissen',
            'Hæfni',
            'Visku',
            'Mahara',
            'Hikma',
            'Dovednost',
            'Sabedoria',
            'Chishiki',
            'Eolas',
            'Scil',
            'Akamai',
            'Jìnéng',
            'Zhīshì',
            'Gisul',
            'Tiṟamai',
            'Aṟivu',
        ]);

        $newPet = $petFactory->createPet(
            $user, $jellingName, $jelling, '', '', FlavorEnum::getRandomValue($rng), MeritRepository::findOneByName($em, MeritEnum::SAGA_SAGA)
        );

        $newPet
            ->increaseLove(10)
            ->increaseSafety(10)
            ->increaseEsteem(10)
            ->increaseFood(-8)
            ->setScale($rng->rngNextInt(80, 120))
            ->addMerit(MeritRepository::findOneByName($em, MeritEnum::AFFECTIONLESS))
        ;

        $newPet->getHouseTime()->setSocialEnergy(-365 * 24 * 60);

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $newPet->setLocation(PetLocationEnum::DAYCARE);
            $message .= "and floats into the daycare as if swimming through the air...";
        }
        else
            $message .= "and floats into your house as if swimming through the air...";

        PetColorFunctions::recolorPet($rng, $newPet);

        $em->flush();

        $responseService->setReloadPets(true);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/weird-blue/{inventory}/hatch", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function hatchWeirdBlueEgg(
        Inventory $inventory, ResponseService $responseService,
        EntityManagerInterface $em, PetFactory $petFactory, IRandom $rng,
        InventoryService $inventoryService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'egg/weird-blue/#/hatch');

        $starMonkey = PetSpeciesRepository::findOneByName($em, PetSpeciesName::StarMonkey);

        if(!$starMonkey)
            throw new \Exception('The species "Star Monkey" does not exist! :| Make Ben fix this!');

        $location = $inventory->getLocation();

        if($location !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('You can\'t hatch it here! Take it to your house, quick!');

        $increasedPetLimitWithEgg = UserQuestRepository::findOrCreate($em, $user, 'Increased Pet Limit with Weird, Blue Egg', false);
        $increasedPetLimitWithMetalBox = UserQuestRepository::findOrCreate($em, $user, 'Increased Pet Limit with Metal Box', false);

        $getAPet = (!$increasedPetLimitWithEgg->getValue() && !$increasedPetLimitWithMetalBox->getValue())
            || $rng->rngNextInt(1, 3) === 1;

        $em->remove($inventory);

        if($getAPet)
        {
            $message = "Whoa! A weird creature popped out! It kind of looks like a monkey, but without arms. Also: a glowing tail. (Also: I feel like monkeys don't hatch from eggs?)";

            if(!$increasedPetLimitWithEgg->getValue() && !$increasedPetLimitWithMetalBox->getValue())
            {
                $user->increaseMaxPets(1);
                $increasedPetLimitWithEgg->setValue(true);

                $message .= "\n\nAlso, your maximum pet limit at home has been increased by one!? Sure, why not! (But just this once!)";
            }

            $message .= "\n\nAnyway, it's super cute, and... really seems to like you! In fact, it's already named itself after you??";

            $monkeyName = $rng->rngNextFromArray([
                'Climbing',
                'Fuzzy',
                'Howling',
                'Monkey',
                'Naner',
                'Poppy',
                'Stinky',
                'Tree',
            ]) . ' ' . $user->getName();

            $newPet = $petFactory->createPet(
                $user, $monkeyName, $starMonkey, '', '', FlavorEnum::getRandomValue($rng), MeritRepository::getRandomStartingMerit($em, $rng)
            );

            $newPet
                ->increaseLove(10)
                ->increaseSafety(10)
                ->increaseEsteem(10)
                ->increaseFood(-8)
                ->setScale($rng->rngNextInt(80, 120))
            ;

            $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

            if($numberOfPetsAtHome >= $user->getMaxPets())
            {
                $newPet->setLocation(PetLocationEnum::DAYCARE);
                $message .= "\n\nBut, you know, your house is full, so into the daycare it goes, I guess!";
            }

            PetColorFunctions::recolorPet($rng, $newPet);

            $responseService->setReloadPets();
        }
        else
        {
            $message = "Whoa! A weird creat-- er, wait... that's just a scroll! The egg contained... a scroll??";

            $inventoryService->receiveItem('Scroll of the Star Monkey', $user, $user, $user->getName() . ' found this in a Weird, Blue Egg! (What a weird, blue place to find a scroll!)', $inventory->getLocation(), $inventory->getLockedToOwner());
        }

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/metalBox/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openMetalBox(
        Inventory $inventory, ResponseService $responseService,
        EntityManagerInterface $em, PetFactory $petFactory, IRandom $rng,
        InventoryService $inventoryService, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'egg/metalBox/#/open');

        $grabber = PetSpeciesRepository::findOneByName($em, PetSpeciesName::Grabber);

        if(!$grabber)
            throw new \Exception('The species "Grabber" does not exist! :| Make Ben fix this!');

        $location = $inventory->getLocation();

        if($location !== LocationEnum::HOME)
            return $responseService->itemActionSuccess('You can\'t open it here! Take it to your house, quick!');

        $increasedPetLimitWithEgg = UserQuestRepository::findOrCreate($em, $user, 'Increased Pet Limit with Weird, Blue Egg', false);
        $increasedPetLimitWithMetalBox = UserQuestRepository::findOrCreate($em, $user, 'Increased Pet Limit with Metal Box', false);

        $getAPet = (!$increasedPetLimitWithEgg->getValue() && !$increasedPetLimitWithMetalBox->getValue())
            || $rng->rngNextInt(1, 3) === 1;

        $em->remove($inventory);

        if($getAPet)
        {
            $message = "Whoa! A weird creature popped out! It's some kinda' robot! But without arms?";

            if(!$increasedPetLimitWithEgg->getValue() && !$increasedPetLimitWithMetalBox->getValue())
            {
                $user->increaseMaxPets(1);
                $increasedPetLimitWithMetalBox->setValue(true);

                $message .= "\n\n(Also, your maximum pet limit at home has been increased by one! But just this once!)";
            }

            $message .= "\n\nAnyway, it's dashing around like it's excited to be here; it really seems to like you! In fact, it's already named itself after you??";

            $newPet = $petFactory->createPet(
                $user, '', $grabber, '', '', FlavorEnum::getRandomValue($rng), MeritRepository::getRandomStartingMerit($em, $rng)
            );

            PetColorFunctions::recolorPet($rng, $newPet, 0.2);

            $robotName = 'Metal ' . $user->getName() . ' ' . $rng->rngNextFromArray([
                '2.0',
                'Beta',
                'Mk 2',
                '#' . $newPet->getColorA(),
                'X',
                '',
                'RC1',
                'SP2'
            ]);

            $newPet->setName(trim($robotName));

            $newPet
                ->increaseLove(10)
                ->increaseSafety(10)
                ->increaseEsteem(10)
                ->increaseFood(-8)
                ->setScale($rng->rngNextInt(80, 120))
            ;

            $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

            if($numberOfPetsAtHome >= $user->getMaxPets())
            {
                $newPet->setLocation(PetLocationEnum::DAYCARE);
                $message .= "\n\nBut, you know, your house is full, so into the daycare it goes, I guess!";
            }

            $responseService->setReloadPets();
        }
        else
        {
            $message = "You punch the box, causing a leaf, a mushroom, a flower, and 100 moneys worth of coins come out! (That's one way to open a box, I guess...)";

            $inventoryService->receiveItem('Magic Leaf', $user, $user, $user->getName() . ' found this in a Metal Box!', $inventory->getLocation(), $inventory->getLockedToOwner());
            $inventoryService->receiveItem('Toadstool', $user, $user, $user->getName() . ' found this in a Metal Box!', $inventory->getLocation(), $inventory->getLockedToOwner());
            $inventoryService->receiveItem('Sunflower', $user, $user, $user->getName() . ' found this in a Metal Box!', $inventory->getLocation(), $inventory->getLockedToOwner());
            $transactionService->getMoney($user, 100, 'You found this in a Metal Box!');
        }

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
