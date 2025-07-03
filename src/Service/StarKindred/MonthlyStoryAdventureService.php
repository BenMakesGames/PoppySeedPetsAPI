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

namespace App\Service\StarKindred;

use App\Entity\MonthlyStoryAdventureStep;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserMonthlyStoryAdventureStepCompleted;
use App\Enum\LocationEnum;
use App\Enum\StoryAdventureTypeEnum;
use App\Service\InventoryService;
use App\Service\StarKindred\Adventures\RemixAdventuresService;
use App\Service\StarKindred\Adventures\StandardAdventuresService;
use Doctrine\ORM\EntityManagerInterface;

class MonthlyStoryAdventureService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly StandardAdventuresService $standardAdventures,
        private readonly RemixAdventuresService $remixAdventures,
    )
    {
    }

    public function isStepCompleted(User $user, MonthlyStoryAdventureStep $step): bool
    {
        $completedStep = $this->em->getRepository(UserMonthlyStoryAdventureStepCompleted::class)->createQueryBuilder('c')
            ->select('COUNT(c.id) AS qty')
            ->andWhere('c.user=:user')
            ->andWhere('c.adventureStep=:adventureStep')
            ->setParameter('user', $user)
            ->setParameter('adventureStep', $step)
            ->getQuery()
            ->getSingleResult();

        return $completedStep['qty'] > 0;
    }

    public function isPreviousStepCompleted(User $user, MonthlyStoryAdventureStep $step): bool
    {
        $previousStep = $this->em->getRepository(MonthlyStoryAdventureStep::class)->createQueryBuilder('s')
            ->andWhere('s.step=:step')
            ->andWhere('s.adventure=:adventure')
            ->setParameter('step', $step->getPreviousStep())
            ->setParameter('adventure', $step->getAdventure()->getId())
            ->getQuery()
            ->getSingleResult()
        ;

        if(!$previousStep)
            throw new \Exception('Ben has made a terrible error: one of the story adventure steps could not be found. And it totally should have been.');

        return $this->isStepCompleted($user, $previousStep);
    }

    /**
     * @param Pet[] $pets
     */
    public function completeStep(User $user, MonthlyStoryAdventureStep $step, array $pets): string
    {
        $petSkills = array_map(fn(Pet $pet) => $pet->getComputedSkills(), $pets);

        $results = match ($step->getType())
        {
            StoryAdventureTypeEnum::CollectStone => $this->standardAdventures->doCollectStone($step, $petSkills),
            StoryAdventureTypeEnum::Gather => $this->standardAdventures->doGather($step, $petSkills),
            StoryAdventureTypeEnum::Hunt => $this->standardAdventures->doHunt($step, $petSkills),
            StoryAdventureTypeEnum::MineGold => $this->standardAdventures->doMineGold($step, $petSkills),
            StoryAdventureTypeEnum::RandomRecruit => $this->standardAdventures->doRandomRecruit($step, $petSkills),
            StoryAdventureTypeEnum::Story => $this->standardAdventures->doStory($step, $petSkills),
            StoryAdventureTypeEnum::TreasureHunt => $this->standardAdventures->doTreasureHunt($step, $petSkills),
            StoryAdventureTypeEnum::WanderingMonster => $this->standardAdventures->doWanderingMonster($step, $petSkills),
            StoryAdventureTypeEnum::RemixShipwreck => $this->remixAdventures->doShipwreck($step, $petSkills),
            StoryAdventureTypeEnum::RemixBeach => $this->remixAdventures->doBeach($step, $petSkills),
            StoryAdventureTypeEnum::RemixForest => $this->remixAdventures->doForest($step, $petSkills),
            StoryAdventureTypeEnum::RemixCave => $this->remixAdventures->doCave($step, $petSkills),
            StoryAdventureTypeEnum::RemixUndergroundLake => $this->remixAdventures->doUndergroundLake($step, $petSkills),
            StoryAdventureTypeEnum::RemixMagicTower => $this->remixAdventures->doMagicTower($step, $petSkills),
            StoryAdventureTypeEnum::RemixUmbralPlants => $this->remixAdventures->doUmbralPlants($step, $petSkills),
            StoryAdventureTypeEnum::RemixUndergroundVillage => $this->remixAdventures->doUndergroundVillage($step, $petSkills),
            StoryAdventureTypeEnum::RemixGraveyard => $this->remixAdventures->doGraveyard($step, $petSkills),
            StoryAdventureTypeEnum::RemixTheDeep => $this->remixAdventures->doTheDeep($step, $petSkills),
            default => throw new \Exception('Oh, dang: Ben forgot to implement this story adventure type! :('),
        };

        foreach($results->loot as $item)
            $this->inventoryService->receiveItem($item, $user, $user, $user->getName() . ' gave this to their pets during a game of ★Kindred.', LocationEnum::Home);

        $this->markStepComplete($user, $step);

        return $results->text;
    }

    private function markStepComplete(User $user, MonthlyStoryAdventureStep $step): void
    {
        $completedStep = new UserMonthlyStoryAdventureStepCompleted($user, $step);

        $this->em->persist($completedStep);
    }
}