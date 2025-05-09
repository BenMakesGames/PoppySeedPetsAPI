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


namespace App\Service\PetActivity\Daydreams;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class PizzaDaydream
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly TransactionService $transactionService
    )
    {
    }

    public function doAdventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        $adventures = [
            $this->doPizzaDelivery(...),
            $this->doExplorePizzaPlanet(...),
            $this->doMozzarellaCloud(...),
            $this->doBubblegumSauce(...),
        ];

        $log = $this->rng->rngNextFromArray($adventures)($petWithSkills);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $log
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Dream' ]))
            ->setIcon('icons/status-effect/daydream-pizza')
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->setChanges($changes->compare($pet));

        return $log;
    }

    private function doPizzaDelivery(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->rng->rngNextInt(1, 20) === 1 || $pet->getOwner()->getMoneys() < 2)
            return $this->doPizzaDeliveryFromMysteryParents($pet);

        $otherPet = self::getRandomFriend($this->em, $this->rng, $pet);

        if(!$otherPet)
            return $this->doPizzaDeliveryFromMysteryParents($pet);

        // do the stuff for the daydreaming pet:
        if($pet->getOwner()->getId() !== $otherPet->getOwner()->getId())
            $this->transactionService->spendMoney($pet->getOwner(), 2, $pet->getName() . ' tipped a pizza delivery pet in a daydream... which was also apparently real?!?', true);

        $petLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' daydreamed they ordered a pizza, and ' . ActivityHelpers::PetName($otherPet) . ' delivered it to them! ' . ActivityHelpers::PetName($pet) . ' tipped 2~~m~~, and when they snapped back to reality, found they had a Pizza Box!');

        $this->inventoryService->petCollectsItem('Pizza Box', $pet, $pet->getName() . ' had this delivered to them in a daydream!', $petLog);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $petLog);

        // do the stuff for the daydreamed-about pet:
        if($pet->getOwner()->getId() !== $otherPet->getOwner()->getId())
        {
            $this->transactionService->getMoney($otherPet->getOwner(), 2, $otherPet->getName() . ' received a tip for delivering a pizza in a dream... which was also apparently real?!?');

            PetActivityLogFactory::createUnreadLog($this->em, $otherPet, ActivityHelpers::PetName($otherPet) . ' dreamed they delivered a pizza to ' . ActivityHelpers::PetName($pet) . ', who tipped them 2~~m~~. When they woke up, they were holding the 2~~m~~!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Dream' ]))
            ;
        }
        else
        {
            PetActivityLogFactory::createReadLog($this->em, $otherPet, ActivityHelpers::PetName($otherPet) . ' dreamed they delivered a pizza to ' . ActivityHelpers::PetName($pet) . ', who tipped them 2~~m~~. When they woke up, they were holding the 2~~m~~!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Dream' ]))
            ;
        }

        return $petLog;
    }

    private function doPizzaDeliveryFromMysteryParents(Pet $pet): PetActivityLog
    {
        $petLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' daydreamed they ordered a pizza. They couldn\'t quite see the pets that delivered it, but ' . ActivityHelpers::PetName($pet) . ' could tell it was their parents, and they were humming a familiar tune. When they snapped back to reality, they had a Pizza Box!');

        $this->inventoryService->petCollectsItem('Pizza Box', $pet, $pet->getName() . ' had this delivered to them in a daydream!', $petLog);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $petLog);

        return $petLog;
    }

    private function doExplorePizzaPlanet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skillRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        $possibleToppings = [
            'Spicy Peps',
            'Onion',
            'Tomato',
            'Fish',
            'Pineapple',
            'Cheese',
            'Chanterelle',
        ];

        $this->rng->rngNextShuffle($possibleToppings);

        $loot = [
            $possibleToppings[0],
        ];

        $exp = 1;

        if($skillRoll >= 15)
        {
            $loot[] = $possibleToppings[1];
            $exp++;
        }

        if($skillRoll >= 25)
        {
            $loot[] = $possibleToppings[2];
            $exp += 2;
        }

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' daydreamed they were exploring a planet made of pizza. They stayed for a while, ate (a true Gourmand!), and picked up various toppings: ' . ArrayFunctions::list_nice_sorted($loot) . '. When they snapped back to reality, they had everything they picked up in the daydream!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gourmand', 'Eating' ]));

            $pet->increaseFood(8);
        }
        else
        {
            $quantityDescription = count($loot) === 1 ? 'a topping' : 'some toppings';

            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' daydreamed they were exploring a planet made of pizza. While they explored, they picked up ' . $quantityDescription . ': ' . ArrayFunctions::list_nice_sorted($loot) . '. When they snapped back to reality, they had everything they picked up in the daydream!');
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' pulled this from the surface of a pizza planet they daydreamed about...', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $log);

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::NATURE ], $log);

        return $log;
    }

    private function doMozzarellaCloud(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' daydreamed they rode on a mozzarella cloud, raining grated parmesan over enchanted forests of pineapple and ham trees. When they snapped back to reality, they were covered in Cheesy Flakes!');

        $this->inventoryService->petCollectsItem('Cheesy Flakes', $pet, $pet->getName() . ' pulled this from the surface of a pizza planet they daydreamed about...', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $log);

        return $log;
    }

    private function doBubblegumSauce(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' daydreamed they were on a conveyor belt in a pizza factory making bubblegum pizzas. When they got under a giant nozzle dispensing bubblegum sauce, they opened wide and guzzled it down (a true Gourmand!), getting covered in bubblegum sauce in the process. When they snapped back to reality, they were covered in bubblegum!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gourmand', 'Eating' ]));

            $pet->increaseFood(8);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' daydreamed they were on a conveyor belt in a pizza factory making bubblegum pizzas. They tried to jump off, but got covered in bubblegum sauce before they could. When they snapped back to reality, they were covered in bubblegum!');
            $pet->increaseSafety(-2); // :P
        }

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::BUBBLEGUMD, 1);

        return $log;
    }

    private static function getRandomFriend(EntityManagerInterface $em, IRandom $rng, Pet $pet): ?Pet
    {
        $relationshipCount = (int)$em->createQueryBuilder()
            ->select('COUNT(r.id)')
            ->from(PetRelationship::class, 'r')
            ->andWhere('r.pet=:pet')
            ->setParameter('pet', $pet)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if($relationshipCount == 0)
            return null;

        $offset = $relationshipCount == 1 ? 0 : $rng->rngNextInt(0, $relationshipCount - 1);

        $relationship = $em->getRepository(PetRelationship::class)->createQueryBuilder('r')
            ->join('r.relationship', 'f')
            ->andWhere('r.pet=:pet')
            ->setParameter('pet', $pet)
            ->setMaxResults(1)
            ->setFirstResult($offset)
            ->getQuery()
            ->getSingleResult();

        return $relationship->getRelationship();
    }
}