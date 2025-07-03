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
use App\Model\ComputedPetSkills;
use App\Model\MonthlyStoryAdventure\AdventureResult;
use App\Service\IRandom;

class RemixAdventuresService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly StandardAdventuresService $standardAdventures,
    )
    {
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doShipwreck(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        if($this->rng->rngNextBool())
        {
            $result = $this->standardAdventures->doWanderingMonster($step, $pets);

            return new AdventureResult(
                "Entering the shipwreck, there's a group of goblin-like creatures - it looks like they've already cleared the place of valuables, but...\n\n" . $result->text,
                $result->loot
            );
        }
        else
        {
            $result = $this->standardAdventures->doTreasureHunt($step, $pets);

            return new AdventureResult(
                "It looks like nature has been reclaiming the ship for many years. Many people have no doubt already searched it, but who knows...\n\n" . $result->text,
                $result->loot
            );
        }
    }

    public function doBeach(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        if($this->rng->rngNextBool())
        {
            $result = $this->standardAdventures->doHunt($step, $pets);

            return new AdventureResult(
                "A hoard of seagulls, apparently being commanded by a wizard, attack!\n\n" . $result->text,
                $result->loot
            );
        }
        else
        {
            $roll = $this->rng->rngNextInt(1, 20);

            $loot = $this->standardAdventures->getAdventureLoot(
                null,
                $pets,
                fn(ComputedPetSkills $pet) => (int)ceil(($pet->getStrength()->getTotal() + $pet->getDexterity()->getTotal()) / 2) + $pet->getBrawl()->getTotal(),
                $roll,
                'Fish Bag',
                [ 'Scales', 'Fish', 'Coconut', 'Really Big Leaf', 'Naner' ]
            );

            $text = $this->standardAdventures->describeAdventure(
                $pets,
                "The beach is peaceful and calm, giving plenty of time to gather, and go fishing...",
                $roll,
                $loot,
                null
            );

            return new AdventureResult($text, $loot);
        }
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doForest(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doCave(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doUndergroundLake(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doMagicTower(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doUmbralPlants(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doUndergroundVillage(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doGraveyard(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }

    /**
     * @param ComputedPetSkills[] $pets
     */
    public function doTheDeep(MonthlyStoryAdventureStep $step, array $pets): AdventureResult
    {
        // To be implemented
        return new AdventureResult("", []);
    }
}