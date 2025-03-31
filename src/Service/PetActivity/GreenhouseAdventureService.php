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


namespace App\Service\PetActivity;

use App\Entity\GreenhousePlant;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\EnchantmentRepository;
use App\Functions\NumberFunctions;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class GreenhouseAdventureService
{
    function __construct(
        private readonly ResponseService $responseService,
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng,
        private readonly PetExperienceService $petExperienceService,
        private readonly HattierService $hattierService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills, GreenhousePlant $plant): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = 10 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getDexterity()->getTotal();
        $skill = NumberFunctions::clamp($skill, 10, 15);

        $roll = $this->rng->rngNextInt(1, $skill);

        $changes = new PetChanges($pet);

        $this->petExperienceService->gainAffection($pet, 1);

        $harvestOrReplant = $plant->getPlant()->getName() === 'Earth Tree' ? 'replant' : 'harvest';

        $cordial = $pet->hasStatusEffect(StatusEffectEnum::CORDIAL);
        $fun = $cordial ? 'a simply _wonderful_ time' : 'fun';

        $pet->increaseLove(3)->increaseSafety(3)->increaseEsteem(3);

        if($roll <= 8)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had ' . $fun . ' helping %user:' . $pet->getOwner()->getId() . '.name% ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . '.', 'ui/affection');
        }
        else if($roll <= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had ' . $fun . ' helping %user:' . $pet->getOwner()->getId() . '.name% ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . ', and found a Crooked Stick!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had ' . $fun . ' helping %user:' . $pet->getOwner()->getId() . '.name% ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . ', and found a Chanterelle!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Chanterelle', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else if($roll <= 13)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had ' . $fun . ' helping %user:' . $pet->getOwner()->getId() . '.name% ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . ', and found some Witch-hazel!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Witch-hazel', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }
        else //if($roll <= 15)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% had ' . $fun . ' helping %user:' . $pet->getOwner()->getId() . '.name% ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . ', and found a Weird Beetle!', 'ui/affection');
            $this->inventoryService->petCollectsItem('Weird Beetle', $pet, $pet->getName() . ' found this while helping ' . $pet->getOwner()->getName() . ' ' . $harvestOrReplant . ' the ' . $plant->getPlant()->getName() . '.', $activityLog);
        }

        $activityLog
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Greenhouse' ]))
            ->setChanges($changes->compare($pet))
        ;

        return $activityLog;
    }

    public function maybeUnlockBeeAura(Pet $pet, PetActivityLog $activityLog): bool
    {
        $forTheBees = EnchantmentRepository::findOneByName($this->em, 'for the Bees');

        if($this->hattierService->userHasUnlocked($pet->getOwner(), $forTheBees))
            return false;

        $this->hattierService->unlockAuraDuringPetActivity(
            $pet,
            $activityLog,
            $forTheBees,
            'On the way back home, ' . ActivityHelpers::PetName($pet) . ' noticed that a worker bee had made a new home in their hat!',
            'On the way back home, ' . ActivityHelpers::PetName($pet) . ' noticed that a worker bee had followed them home!',
            ActivityHelpers::PetName($pet) . ' was followed home by a worker bee...'
        );

        return true;
    }
}
