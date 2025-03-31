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

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class CachingMeritAdventureService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService
    )
    {

    }

    // foods that are <= 4 food + love
    private const POSSIBLE_CACHED_FOODS = [
        'Beans',
        'Canned Food',
        'Egg',
        'Ginger',
        'Mixed Nuts', // exception: is 5 food + love, but nuts is traditional caching stuffs!
        'Onion',
        'Rice',
    ];

    public function doAdventure(ComputedPetSkills $petWithSkills): ?PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::CACHE_EMPTY))
            return null;

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::CACHE_EMPTY, 24 * 60);

        $pet->increaseFood(4);
        $cacheItem = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray(self::POSSIBLE_CACHED_FOODS));

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% was hungry, so they dug up one of their old food caches on the island, which contained ' . $cacheItem->getNameWithArticle() . '.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;
        $this->inventoryService->petCollectsItem($cacheItem, $pet, $pet->getName() . ' found this in one of their old food caches.', $activityLog);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);

        return $activityLog;
    }
}