<?php
declare(strict_types = 1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Service\StarKindred\Adventures;

use App\Entity\MonthlyStoryAdventureStep;
use App\Enum\StatusEffectEnum;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Model\MonthlyStoryAdventure\AdventureResult;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

class RemixAdventuresService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly StandardAdventuresService $standardAdventures,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doShipwreck(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        switch($this->rng->rngNextInt(1, 3))
        {
            case 1:
                $result = $this->standardAdventures->doWanderingMonster($step, $pets);

                return new AdventureResult(
                    "This shipwreck has apparently been temporarily claimed by group of goblin-like creatures! Well: their stay is about to get a lot... temporarier!\n\n" . $result->text,
                    $result->loot
                );

            case 2:
                return $this->doCustomEncounter(
                    $pets,
                    fn(ComputedPetSkills $pet) => (int)ceil(($pet->getPerception()->getTotal() + $pet->getStamina()->getTotal()) / 2) + $pet->getNature()->getTotal() + $pet->getGatheringBonus()->getTotal(),
                    $this->rng->rngNextFromArray([ 'Ceremonial Trident', 'Secret Seashell', 'Rusted, Busted Mechanism' ]),
                    [ 'Seaweed', 'Silica Grounds', 'Crooked Stick', 'String', 'Rock', 'Rusty Rapier', 'Plastic Bottle', 'Canned Food' ],
                    "It looks like nature has been reclaiming this ship for many years. Many people have no doubt already searched it, but given enough time searching, something will surely turn up?"
                );

            case 3:
                return $this->doCustomEncounter(
                    $pets,
                    fn(ComputedPetSkills $pet) => (int)ceil(($pet->getDexterity()->getTotal() + $pet->getStamina()->getTotal()) / 2) + $pet->getNature()->getTotal() + $pet->getGatheringBonus()->getTotal(),
                    'Seaweed',
                    [ 'Seaweed', 'Seaweed', 'Seaweed', 'Seaweed', 'Crooked Stick', 'Silica Grounds' ],
                    "It looks like nature has been reclaiming this ship for many years... welp: that's a lot of free kelp!"
                );

            default:
                throw new \Exception("Invalid encounter type");
        }
    }

    public function doBeach(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        switch($this->rng->rngNextInt(1, 3))
        {
            case 1:
                $result = $this->standardAdventures->doWanderingMonster($step, $pets);

                return new AdventureResult(
                    "A hoard of seagulls, apparently being commanded by a wizard, attack!\n\n" . $result->text,
                    $result->loot
                );

            case 2:
                return $this->doCustomEncounter(
                    $pets,
                    fn(ComputedPetSkills $pet) => (int)ceil(($pet->getPerception()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getNature()->getTotal() + $pet->getFishingBonus()->getTotal(),
                    'Fish Bag',
                    [ 'Scales', 'Fish', 'Coconut', 'Really Big Leaf', 'Naner' ],
                    "The beach is peaceful and calm, giving plenty of time to gather, and go fishing..."
                );

            case 3:
                return $this->doCustomEncounter(
                    $pets,
                    fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getStamina()->getTotal()) / 2) + $pet->getNature()->getTotal() + $pet->getGatheringBonus()->getTotal(),
                    'Sand Dollar',
                    [ 'Silica Grounds', 'Crooked Stick', 'Seaweed' ],
                    "There's little to find on this part of the beach besides sand, sticks, and seaweed..."
                );

            default:
                throw new \Exception("Invalid encounter type");
        }
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doForest(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // dragon, or bandit encampment, or forest spirit:
        return match($this->rng->rngNextInt(1, 3))
        {
            // dragon:
            1 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
                $this->rng->rngNextFromArray([ 'Nature Box', 'Monster Box' ]),
                [ 'Silver Bar', 'Scales', 'Striped Microcline', 'Magic Leaf', 'Crooked Stick', 'Dragon Tongue', 'Bag of Fertilizer', 'Burnt Log' ],
                "A forest dragon has apparently made a home here. It guards an unconventional treasure, but treasure is treasure!"
            ),
            // bandit encampment:
            2 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
                'Wrapped Sword',
                [ 'White Cloth', 'Stereotypical Torch', 'Gold Bar', 'Fish Stew', 'Takoyaki', 'Kilju', 'Grilled Fish', 'Onigiri', 'Potato' ],
                "In the center of the forest is a makeshift encampment of bandits. They weren't expecting visitors, and they don't exactly have a welcoming attitude."
            ),
            // forest spirit:
            3 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getPerception()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal() + $pet->getUmbraBonus()->getTotal(),
                'Monster-summoning Scroll',
                [ 'Crooked Stick', 'Quintessence', 'Quintessence', 'Talon', 'Dark Scales', 'Music Note' ],
                "The canopy deepens, and the air grows cooler. This forgotten area of the forest has fallen closer to the Umbra, and been overrun by restless animal spirits!"
            ),
            default => throw new \Exception("Invalid encounter type"),
        };
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doCave(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return match($this->rng->rngNextInt(1, 4))
        {
            1 => $this->standardAdventures->doMineGold($step, $pets),
            2 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getStamina()->getTotal()) / 2) + $pet->getNature()->getTotal() + $pet->getMiningBonus()->getTotal(),
                'Box of Ores',
                [ 'Rock', 'Rock', 'Rock', 'Silica Grounds', 'Silica Grounds', 'Silica Grounds', 'Gypsum' ],
                "There's little to find in this part of the caves besides rock, dirt, and sand..."
            ),
            3 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getStamina()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
                'Sand-covered... Something',
                [ 'Silica Grounds', 'Limestone', 'Limestone', 'Rock' ],
                "Why are so many golems made of Limestone? You can try asking one, but I doubt you'll get an answer..."
            ),
            4 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getPerception()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
                'Dark Matter',
                [ 'Dark Matter', 'Fluff', 'Talon', 'Small Bag of Fertilizer' ],
                "One of the main problems with caves is the dark. Another is poop-filled bats. Either on its own would be manageable, but combined?!"
            ),
            default => throw new \Exception("Invalid encounter type"),
        };
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doUndergroundLake(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        switch($this->rng->rngNextInt(1, 3))
        {
            case 1:
                return $this->doCustomEncounter(
                    $pets,
                    fn(ComputedPetSkills $pet) => (int)ceil(($pet->getPerception()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getNature()->getTotal(),
                    'Fish Bag',
                    [ 'Toadstool', 'Toadstool', 'Rock', 'Chanterelle', 'Chanterelle' ],
                    "The lake is home to a variety of mushrooms and fish. Most seem edible, anyway..."
                );

            case 2:
                foreach($pets as $pet)
                    StatusEffectHelpers::applyStatusEffect($this->em, $pet->getPet(), StatusEffectEnum::FatedSoakedly, 1);

                return new AdventureResult("Staring into the lake, the figure of a person is seen, as if standing directly behind you...\n\n(Your pets are now Fated (Soakedly)!)", []);

            case 3:
                return $this->doCustomEncounter(
                    $pets,
                    fn(ComputedPetSkills $pet) => (int)ceil(($pet->getPerception()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getNature()->getTotal(),
                    'Ice Mango',
                    [ 'Everice', 'Everice', 'Everice', 'Fish Bones', 'Cobweb', 'Quintessence' ],
                    "Typically the deeper you go the hotter it gets, but here cold air can be seen rising off the surface of the lake. What could be responsible for such a phenomenon?"
                );
            default:
                throw new \Exception("Invalid encounter type");
        }
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doMagicTower(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return match($this->rng->rngNextInt(1, 3))
        {
            1 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getStamina()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
                'Tower Chest',
                [ 'Talon', 'Silver Bar', 'Fluff', 'Scales', 'Stereotypical Bone', 'Limestone', 'Iron Sword', 'Quintessence' ],
                "It's like the Tower of Trials - floor after floor of monsters, with the occasional puzzle thrown in for a little extra flavor. (At least this one doesn't need a keyblade to enter!)"
            ),
            2 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getPerception()->getTotal() + $pet->getArcana()->getTotal()) / 2) + $pet->getMagicBindingBonus()->getTotal(),
                $this->rng->rngNextFromArray([ 'Scroll of Illusions', 'Scroll of Dice' ]),
                [
                    'Quintessence', 'Tiny Scroll of Resources', 'Crystal Ball', 'Silver Bar', 'Glass',
                    'Mikronium', 'Megalium', 'Gold Tuning Fork', 'Quinacridone Magenta Dye', 'White Cloth',
                    'Viscaria', 'Wolf\'s Bane', 'Witch-hazel', 'Liquid-hot Magma',
                ],
                "This tower, though long abandoned, was clearly once home to a powerful wizard. Many of the rooms are empty, but several remain untouched behind magic seals..."
            ),
            3 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
                $this->rng->rngNextFromArray([ 'Monster Chest', 'Gold Chest', 'Ruby Chest' ]),
                [ 'Talon', 'Scales', 'Gold Bar', 'Gold Bar', 'Gold Bar', 'Silver Bar', 'Silver Bar', 'Dino Skull', 'Gold Key', 'Silver Colander', 'Liquid-hot Magma' ],
                "Though many dragons live inside caves, some find more comfortable homes, like magic towers... inside caves!"
            ),
            default => throw new \Exception("Invalid encounter type"),
        };
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doUmbralPlants(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return match($this->rng->rngNextInt(1, 2))
        {
            1 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => $pet->getDexterity()->getTotal() + $pet->getNature()->getTotal() + $pet->getGatheringBonus()->getTotal(),
                'Nature Box',
                [
                    'Purple Corn',
                ],
                "Tall, purple plants grow here, blowing in the wind."
            ),
            2 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => $pet->getStrength()->getTotal() + $pet->getNature()->getTotal() + $pet->getGatheringBonus()->getTotal(),
                'Monster Box',
                [
                    'Tentacle',
                ],
                "From a distance they look like tall, purple plants blowing in a wind. However..."
            ),
            default => throw new \Exception("Invalid encounter type"),
        };
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doUndergroundVillage(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        $result = $this->standardAdventures->doRandomRecruit($step, $pets);

        return new AdventureResult(
            "In a small village deep underground, one of its inhabitants is restless, looking for an opportunity to strike out on their own adventure...\n\n" . $result->text,
            $result->loot
        );
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doGraveyard(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return $this->doCustomEncounter(
            $pets,
            fn(ComputedPetSkills $pet) => (int)ceil(($pet->getDexterity()->getTotal() + $pet->getStrength()->getTotal()) / 2) + $pet->getBrawl()->getTotal() + $pet->getUmbraBonus()->getTotal(),
            'Blackonite',
            [ 'Rock', 'Quintessence', 'Quintessence', 'Filthy Cloth', 'Grandparoot' ],
            "A graveyard deep beneath the surface of the earth? It's hard to think of many things that could be more haunted."
        );
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doTheDeep(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return match($this->rng->rngNextInt(1, 2))
        {
            1 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStamina()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getGatheringBonus()->getTotal() + $pet->getMiningBonus()->getTotal(),
                'Firestone',
                [
                    'Liquid-hot Magma', 'Liquid-hot Magma', 'Liquid-hot Magma', 'Liquid-hot Magma', 'Liquid-hot Magma', 'Liquid-hot Magma',
                    'Iron Ore', 'Iron Ore', 'Silver Ore', 'Gold Ore',
                    'Striped Microcline'
                ],
                "Deep underground, rivers of magma dimly illuminate long, winding passages."
            ),
            2 => $this->doCustomEncounter(
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStamina()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getGatheringBonus()->getTotal() + $pet->getMiningBonus()->getTotal(),
                'Monster Box',
                [
                    'Tentacle', 'Talon', 'Scales', 'Dark Matter', 'Gravitational Waves', 'Quintessence',
                ],
                "What horrors lie hidden deep within the earth? Even in close-combat, the creatures are difficult to see, but they are large, loud, and lighting-fast."
            ),
            default => throw new \Exception("Invalid encounter type"),
        };
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doTreasureRoom(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        return $this->doCustomEncounter(
            $pets,
            fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getGatheringBonus()->getTotal(),
            $this->rng->rngNextFromArray([ 'Monster Chest', 'Gold Chest', 'Ruby Chest' ]),
            [
                'Gold Bar', 'Gold Bar', 'Gold Bar', 'Gold Bar', 'Gold Bar', 'Gold Bar',
                'Silver Bar', 'Silver Bar', 'Silver Bar', 'Silver Bar',
                'Gold Key', 'Silver Key',
                'Gold Triangle', 'Silver Colander',
                'Scroll of Resources', 'Tiny Scroll of Resources', 'Firestone', '"Gold" Idol',
                'Rib', 'Scroll of Tell Samarzhoustian Delights', 'Gold Ring',
            ],
            "Hot dang! A room full of riches! It's like the treasure room of a dragon, but... without the dragon? Hm... might not want to stay too long, just in case...",
        );
    }

    private function doCustomEncounter(array $pets, callable $petSkillFn, string $freeLoot, array $lootTable, string $narrative): AdventureResult
    {
        $roll = $this->rng->rngNextInt(1, 20);

        $loot = $this->standardAdventures->getAdventureLoot(
            null,
            $pets,
            $petSkillFn,
            $roll,
            $freeLoot,
            $lootTable
        );

        $text = $this->standardAdventures->describeAdventure(
            $pets,
            $narrative,
            $roll,
            $loot,
            null
        );

        return new AdventureResult($text, $loot);
    }
}