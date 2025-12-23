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

namespace App\Service\PetActivity\Group;

use App\Entity\Pet;
use App\Entity\PetGroup;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetSkillEnum;
use App\Functions\GroupNameGenerator;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use Doctrine\ORM\EntityManagerInterface;

class SportsBallService
{
    public const string ActivityIcon = 'groups/sportsball';

    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly PetRelationshipService $petRelationshipService,
        private readonly IRandom $rng
    )
    {
    }

    private const array Dictionary = [
        'adjective' => [
            'Lucky', 'Wild', 'Mad', 'Magic', 'Fierce', 'Feisty', 'Island', 'Jungle', 'Marvelous', 'Tough',
            'Amazing', 'Seaside', 'Umbral', 'Digital', 'Frost-born', 'Gorgeous', 'Midnight', 'Giant', 'Lightning',
            'Royal', 'Striped', 'Fleeting', 'Rising', 'Flying', 'Relentless', 'Storm', 'Baleful', 'Menacing',
            'Puckish', 'Tenacious', 'Fantastic', 'Brave', 'Fancy', 'Graceful', 'Sly', 'Wicked'
        ],
        'nouns' => [
            'Rockets', 'Bulls', 'Bears', 'Dolphins', 'Eagles', 'Elephants', 'Sharks', 'Tigers', 'Lions', 'Panthers',
            'Fighters', 'Reapers', 'Captains', 'Riders', 'Krakens', 'Howlers', 'Oracles', 'Knights', 'Jesters',
            'Giants', 'Wizards', 'Witches', 'Rhinos', 'Sabers', 'Wolves', 'Pelicans', 'Whales', 'Warriors',
            'Rogues', 'Turtles', 'Rats', 'Cats', 'Rams', 'Coyotes', 'Penguins', 'Pirates', 'Dancers', 'Lancers',
            'Wanderers', 'Braves', 'Jesters', 'Viceroys', 'Margraves'
        ],
    ];

    private const array GroupNamePatterns = [
        '%adjective% %nouns%',
    ];

    private const array PossibleLoot = [
        'Green Sportsball Ball',
        'Orange Sportsball Ball',
        'Sportsball Pin',
        'Sportsball Oar',
    ];

    public function generateGroupName(): string
    {
        return GroupNameGenerator::generateName($this->rng, self::GroupNamePatterns, self::Dictionary, 60);
    }

    private function rollSkill(Pet $pet): int
    {
        $petWithSkills = $pet->getComputedSkills();
        $total =
            max($petWithSkills->getStrength()->getTotal(), $petWithSkills->getStamina()->getTotal()) +
            max($petWithSkills->getDexterity()->getTotal(), $petWithSkills->getPerception()->getTotal()) +
            (int)($petWithSkills->getBrawl()->getTotal() / 2) +
            (int)($petWithSkills->getStealth()->getTotal() / 4)
        ;

        if($pet->hasMerit(MeritEnum::LUCKY))
            $total += 3;

        return $this->rng->rngNextInt(1, 25 + $total);
    }

    public function meet(PetGroup $group): void
    {
        $message = '%pet% got together with %group% and played some Sportsball.';

        $petSkills = [];

        foreach($group->getMembers() as $member)
            $petSkills[$member->getId()] = $this->rollSkill($member);

        asort($petSkills);
        $lowestPerformer = array_key_first($petSkills);
        $highestPerformer = array_key_last($petSkills);

        foreach($group->getMembers() as $member)
        {
            $messageTemplate = $message;
            $petChanges = new PetChanges($member);

            if($member->getId() === $highestPerformer)
            {
                $messageTemplate .= ' They were the star performer!';

                $member->increaseEsteem($this->rng->rngNextInt(4, 7));
            }
            else if($member->getId() === $lowestPerformer)
            {
                if($member->getEsteem() < 0)
                    $messageTemplate .= ' They didn\'t do very well...';
                else
                {
                    $messageTemplate .= ' They didn\'t do very well, but it was still fun.';
                    $member->increaseEsteem($this->rng->rngNextInt(2, 5));
                }
            }
            else
                $member->increaseEsteem($this->rng->rngNextInt(3, 6));

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $member, $this->formatMessage($messageTemplate, $member, $group))
                ->setIcon(self::ActivityIcon)
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout', 'Sportsball' ]))
            ;

            $this->petExperienceService->gainExp($member, 1, [
                PetSkillEnum::Brawl,
                PetSkillEnum::Brawl,
                PetSkillEnum::Stealth,
            ], $activityLog);

            if($this->rng->rngNextInt(1, 10) === 1 && $member->getId() !== $lowestPerformer)
            {
                $loot = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray(self::PossibleLoot));
                $activityLog->appendEntry($member->getName() . ' accidentally brought ' . $loot->getNameWithArticle() . ' home after the game. (Oops! (Oh well.))');
                $this->inventoryService->petCollectsItem($loot, $member, $this->formatMessage($message, $member, $group), $activityLog);
            }

            $activityLog->setChanges($petChanges->compare($member));
        }

        $this->petRelationshipService->groupGathering(
            $group->getMembers(),
            '%p1% and %p2% talked a little while playing Sportsball with ' . $group->getName() . '.',
            '%p1% and %p2% avoided talking as much as possible while playing Sportsball with ' . $group->getName() . '.',
            'Met during a ' . $group->getName() . ' game.',
            '%p1% met %p2% during a ' . $group->getName() . ' game.',
            [ PetActivityLogTagEnum::Sportsball ],
            100
        );

        $group->setLastMetOn();
    }

    private function formatMessage(string $template, Pet $member, PetGroup $group): string
    {
        return str_replace(
            [
                '%pet%',
                '%group%',
            ],
            [
                $member->getName(),
                $group->getName(),
            ],
            $template
        );
    }
}
