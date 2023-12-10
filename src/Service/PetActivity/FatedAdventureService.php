<?php

namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Entity\StatusEffect;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class FatedAdventureService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly InventoryService $inventoryService
    )
    {
    }

    /**
     * @throws PSPNotFoundException
     * @throws EnumInvalidValueException
     */
    public function maybeResolveFate(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        /** @var StatusEffect $fatedStatusEffect */
        $fatedStatusEffect = ArrayFunctions::find_one($pet->getStatusEffects()->toArray(), fn(StatusEffect $se) =>
            in_array($se->getStatus(), [
                StatusEffectEnum::FATED_DELICIOUSNESS,
                StatusEffectEnum::FATED_SOAKEDLY,
                StatusEffectEnum::FATED_ELECTRICALLY,
                StatusEffectEnum::FATED_FERALLY,
                StatusEffectEnum::FATED_LUNARLY
            ])
        );

        if(!$fatedStatusEffect)
            return null;

        $oneInX = 100 - $fatedStatusEffect->getCounter();

        if($oneInX > 1 && $this->rng->rngNextInt(1, $oneInX) > 1)
        {
            $fatedStatusEffect->incrementCounter();
            return null;
        }

        $fateName = $fatedStatusEffect->getStatus();

        $changes = new PetChanges($pet);

        $log = match ($fateName)
        {
            StatusEffectEnum::FATED_DELICIOUSNESS => $this->doDeliciousFate($petWithSkills),
            StatusEffectEnum::FATED_SOAKEDLY => $this->doWateryFate($petWithSkills),
            StatusEffectEnum::FATED_ELECTRICALLY => $this->doElectricFate($petWithSkills),
            StatusEffectEnum::FATED_FERALLY => $this->doFurryFate($petWithSkills),
            StatusEffectEnum::FATED_LUNARLY => $this->doLunarFate($petWithSkills),
            default => throw new \Exception("Unsupported fate! Ben made a terrible mistake!"),
        };

        $pet->removeStatusEffect($fatedStatusEffect);

        $log
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
        ;

        return $log;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function doDeliciousFate(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal());

        if($roll < 15)
        {
            if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was about to go out gathering, when they spotted a drain pipe in the town they\'d never seen before. They went inside, and found a room occupied by a light-based puzzle! (Every game\'s got to have one, I guess!) It proved too difficult, however, and ' . ActivityHelpers::PetName($pet) . ' eventually gave up and returned home. Curiously, once home, they found that they were unable to recall the location of the puzzle room (despite their Eidetic Memory - magic?!?), and sensed that they had failed to realize their delicious fate...')
                    ->setIcon('icons/activity-logs/confused');
            }
            else
            {
                $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was about to go out gathering, when they spotted a drain pipe in the town they\'d never seen before. They went inside, and found a room occupied by a light-based puzzle! (Every game\'s got to have one, I guess!) It proved too difficult, however, and ' . ActivityHelpers::PetName($pet) . ' eventually gave up and returned home. Curiously, once home, they found that they were unable to recall the location of the puzzle room, and sensed that they had failed to realize their delicious fate. (Darn!)')
                    ->setIcon('icons/activity-logs/confused');
            }

            $pet->increaseEsteem(-4);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::OTHER, null);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ], $log);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was about to go out gathering, when they spotted a drain pipe in the town they\'d never seen before. They went inside, and found a room occupied by a light-based puzzle! (Every game\'s got to have one, I guess!) After rotating various mirrors, ' . ActivityHelpers::PetName($pet) . ' unlocked a secret chamber containing a wealth of exotic foods and other treasures! They returned home, laden with goods, and sensing that they had successfully realized their delicious fate! (Neat!)');

            $pet->increaseEsteem(12);

            $loot = $this->rng->rngNextSubsetFromArray([
                'Scroll of Tell Samarzhoustian Delights', 'Bizet Cake', 'Blood Wine', 'Blueberry Jam', 'Cacao Fruit',
                'Candied Lotus Petals', 'Chocolate-covered Honeycomb', 'Cup of Life', 'Dandelion Wine',
                'Everlasting Syllabub', 'Fruits & Veggies Box', 'Goodberries', 'Ginger Beer', 'Ginger Beer', 'Mochi',
                'Pavé aux Noix', 'Qabrêk Splàdj', 'Regular-sized Pumpkin', 'Thicc Mints', 'Scroll of Fruit',
            ], 8);

            sort($loot);

            foreach($loot as $item)
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in a mysterious puzzle room beneath the town, and in so doing fulfilled their delicious fate.', $log);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::OTHER, null);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ], $log);
        }

        return $log;
    }

    /**
     * @throws PSPNotFoundException
     * @throws EnumInvalidValueException
     */
    private function doWateryFate(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getPerception()->getTotal());

        if($roll < 15)
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was fishing on the beach when they spotted something... odd beneath the surface of the water. They dived in, and found a sunken ship with a locked safe. They tried to pick the lock, but ran out of breath and had to resurface. Curiously, once above the water, they could no longer see any sign of the sunken ship, and sensed that they had failed to realize their watery fate. (Darn!)')
                ->setIcon('icons/activity-logs/confused');

            $pet->increaseEsteem(-4);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::OTHER, null);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $log);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was fishing on the beach when they spotted something... odd beneath the surface of the water. They dived in, and found a sunken ship with a locked safe. It took some time, and they almost ran out of breath, but ' . ActivityHelpers::PetName($pet) . ' was able to pick the lock and grab the treasure inside before returning to the surface. Having done so, they sensed that they had successfully realized their watery fate! (Neat!)');

            $pet->increaseEsteem(12);

            $curiousEnchantment = EnchantmentRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Bright',
                'Cursed',
                'Burnt',
                'Piercing',
                'Captain\'s',
                'Triangulating',
                'Frumious',
                'Magnetic',
                'of Tides',
                'Fearsome',
            ]));

            $loot = $this->rng->rngNextSubsetFromArray([
                'Gold Chest', 'Gold Ring', 'Gold Key', 'Tile: Gold Vein', 'Silver Bar', 'Shiny Pail', 'Blackonite',
                'Black Flag', 'Gold Dragon Ingot', 'Compass', 'Magic Hourglass', 'Gypsum Dragon', 'Magic Mirror',
                'Kumis', 'Minor Scroll of Riches', 'Scroll of Resources', 'Scroll of the Sea',
            ], 6);

            $loot[] = 'Gold Bar';
            $loot[] = 'Curious Cutlass';

            sort($loot);

            foreach($loot as $item)
            {
                $enchantment = $item === 'Curious Cutlass'
                    ? $curiousEnchantment
                    : null;

                $this->inventoryService->petCollectsEnhancedItem($item, $enchantment, null, $pet, $pet->getName() . ' found this in safe in a sunken ship, and in so doing fulfilled their watery fate.', $log);
            }

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::OTHER, null);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $log);
        }

        return $log;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function doElectricFate(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was going to engineer something, when they accidentally shocked themselves and passed out! They woke up a few minutes later, head swirling with ideas and images they couldn\'t quite make out, but a feeling of overwhelming inspiration took hold of their mind, and ' . ActivityHelpers::PetName($pet) . ' sensed that their shocking fate was coming to fruition! (Neat!)');

        $pet->increaseEsteem(12);

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::INSPIRED, 24 * 60);
        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::OUT_OF_THIS_WORLD, 24 * 60);
        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::VIVACIOUS, 24 * 60);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(10, 20), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::SCIENCE ], $log);

        return $log;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function doLunarFate(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was out gathering in the woods, when they happened to spot a Moon Pearl on the ground. After picking it up, they found themselves surrounded by a cloud of moths! Many of the moths flew off into various parts of the woods, but several landed on ' . ActivityHelpers::PetName($pet) . ', and showed no intention of leaving. They returned home covered in moths, and sensing they had somehow realized their moon-y fate! (Neat!)');

        $moths = $this->rng->rngNextInt(9, 15);

        $this->inventoryService->petCollectsItem('Moon Pearl', $pet, $pet->getName() . ' found this on the ground in the woods, and in so doing apparently fulfilled their moon-y fate.', $log);

        for($i = 0; $i < $moths; $i++)
            $this->inventoryService->petCollectsItem('Moth', $pet, $pet->getName() . ' met this moth (and several of its friends!) in the woods, and in so doing apparently fulfilled their moon-y fate.', $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE ], $log);

        return $log;
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function doFurryFate(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal());

        if($roll < 15)
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was out gathering in the woods, when they spotted a lone wolf. It was huge, and the two locked eyes for a moment, neither daring to move. ' . ActivityHelpers::PetName($pet) . ' decided to try to communicate with it, but the wolf ran at the first sign of movement. ' . ActivityHelpers::PetName($pet) . ' found themselves alone in the woods, sensing that they had failed to realize their furry, feral fate. (Darn!)')
                ->setIcon('icons/activity-logs/confused');

            $pet->increaseEsteem(-4);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::OTHER, null);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $log);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was out gathering in the woods, when they spotted a lone wolf. It was huge, and the two locked eyes for a moment, neither daring to move. ' . ActivityHelpers::PetName($pet) . ' decided to try to communicate with it. At first it looked like the wolf might run away, but after listening for a moment, it spoke in yips and yaps, motioned towards the ground, and vanished into the woods. ' . ActivityHelpers::PetName($pet) . ' went to where the wolf had been standing, pulled a half-buried something out of the ground, and sensed that they had successfully realized their furry, feral fate. (Neat!)');

            $pet->increaseEsteem(12);

            $this->inventoryService->petCollectsItem('Wolf\'s Favor', $pet, $pet->getName() . ' was given this by an enormous lone wolf they met in the woods, and in so doing fulfilled their furry, feral fate.', $log);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::OTHER, null);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $log);
        }

        return $log;
    }
}