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
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\EnchantmentRepository;
use App\Functions\GroupNameGenerator;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use Doctrine\ORM\EntityManagerInterface;

class GamingGroupService
{
    public const string ActivityIcon = 'groups/gaming';

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
            'Lucky', 'Feisty', 'Wild', 'Random', 'Strategic', 'Secret', 'Winning', 'High-scoring', 'Plastic',
            'Cardboard', 'Digital', 'Elusive', 'Nat-20', 'Full-motion', 'Real-time', 'Grid-based', '360',
            'Trigger-happy', 'Painted', 'Elfin', 'Unpredictable', 'Steady', 'OP', 'Nerfed', 'Meta',
            'Gilded', 'Pixel-perfect', 'Turtling', 'Rushing', 'Countering', 'Six-sided', 'Twenty-sided',
            'Well-shuffled', 'Royal', 'Ace', 'Magic'
        ],
        'nounjective' => [
            'Living Room', 'Fireside', 'Sleepover', 'Breakfast', 'Midnight', 'Refrigerator', 'Teatime',
            'Galaxy', 'Co-op', 'Basement', 'Attic', 'Table-top', 'Poppy Seed', 'D-pad',
            'Fun-time', 'Trick', 'Action', 'Wombo-combo', 'Pen n\' Paper',
        ],
        'noun' => [
            'Society', 'Party', 'Squad', 'Club', 'Guild', 'Order', 'Group', 'Company', 'Café', 'Diner',
        ],
        'nouns' => [
            'Mavericks', 'Geeks', 'Gamers', 'Meeples', 'Pawns', 'Runners', 'Kingmakers', 'Winners', 'Pets',
            'Friends', 'Fans', 'Allies', 'Knights',
        ],
        'number' => [
            'Seven', '13', 'Four', 'Three', 'Twin', '99', '42',
        ],
        'periodicity' => [
            'the First', 'the Last', 'the Final', 'the Original', 'the Only',
        ]
    ];

    private const array GroupNamePatterns = [
        '%noun% of the? %periodicity%/%adjective%/%nounjective% %nouns%',
        '%periodicity% %adjective% %noun%/%nouns%',
        'the? %adjective% %nounjective%? %nouns%/%noun%',
        'the? %number%/%periodicity%? %adjective%/%nounjective% %nouns%',
        '%adjective%/%nounjective% %nouns% and %adjective%/%nounjective% %nouns%',
        '%periodicity%? %number% %nounjective%/%adjective% %nouns%',
        '%adjective% , %adjective% , and %adjective%',
        'the %nounjective% %nounjective% %noun%/%nouns%',
    ];

    public function generateGroupName(): string
    {
        return GroupNameGenerator::generateName($this->rng, self::GroupNamePatterns, self::Dictionary, 60);
    }

    /**
     * @param string[] $skills
     */
    private function rollSkill(Pet $pet, array $skills): int
    {
        $total = 0;

        foreach($skills as $skill)
        {
            $total += match ($skill)
            {
                'luck' => $pet->hasMerit(MeritEnum::LUCKY) ? 8 : 0,
                'extroversion' => ($pet->getExtroverted() + 1) * 5 + $pet->getBonusMaximumFriends() * 2,
                default => $pet->getSkills()->getStat($skill),
            };
        }

        return $this->rng->rngNextInt(1, 20 + $total);
    }

    private const string NameScrawlful2 = 'Scrawlful 2';

    private const string TypeFighting = 'fighting';
    private const string TypeRhythm = 'rhythm';
    private const string TypeBoard = 'board';
    private const string TypeParty = 'party';
    private const string TypeTTRPG = 'TTRPG';
    private const string TypeLARPing = 'LARPing';

    public function meet(PetGroup $group): void
    {
        $partyGameName = $this->rng->rngNextFromArray([ 'Reds to Reds', self::NameScrawlful2, 'Mixit', 'One-night Werecreature' ]);

        $game = $this->rng->rngNextFromArray([
            [
                'type' => self::TypeFighting,
                'name' => $this->rng->rngNextFromArray([ 'Hyper Smash Sisters', 'Spiritcalibur II' ]),
                'winWith' => [ 'dexterity', 'perception', 'intelligence' ],
                'exp' => null,
                'possibleLoot' => null,
                'lootMessage' => null,
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TypeRhythm,
                'name' => $this->rng->rngNextFromArray([ 'Dance Dance Uprising', 'Guitar Champion' ]),
                'winWith' => null,
                'exp' => [ PetSkillEnum::MUSIC ],
                'possibleLoot' => [ 'Music Note' ],
                'lootMessage' => 'They played through a ton of songs!',
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TypeBoard,
                'name' => $this->rng->rngNextFromArray([ 'Settlers of Hollow Earth', 'Galaxy Hauler' ]),
                'winWith' => [ 'luck' ],
                'exp' => null,
                'possibleLoot' => [ 'Glowing Six-sided Die' ],
                'lootMessage' => 'Somehow they ended up with more dice than they started with...',
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TypeBoard,
                'name' => $this->rng->rngNextFromArray([ 'Naner Farmer', 'Spice Magnate', 'Terraforming Ganymede', 'Gemstone Alchemist' ]),
                'winWith' => [ 'intelligence' ],
                'exp' => null,
                'possibleLoot' => null,
                'lootMessage' => null,
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TypeParty,
                'name' => $partyGameName,
                'winWith' => [ 'extroversion', 'luck' ],
                'exp' => ($partyGameName === self::NameScrawlful2) ? [ PetSkillEnum::CRAFTS ] : null,
                'possibleLoot' => null,
                'lootMessage' => null,
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TypeTTRPG,
                'name' => 'Keeps and Colossi',
                'winWith' => null,
                'exp' => null,
                'possibleLoot' => [ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ],
                'lootMessage' => 'Somehow they ended up with more dice than they started with...',
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TypeLARPing,
                'name' => 'LARPing',
                'winWith' => null,
                'exp' => [ PetSkillEnum::STEALTH, PetSkillEnum::CRAFTS ],
                'possibleLoot' => [ 'Scythe', 'Hunting Spear', 'Snakebite' ],
                'lootMessage' => 'They made their own, foam weapons to play with!',
                'lootEnchantments' => [ 'Foam' ],
            ]
        ]);

        $message = '%pet% got together with %group% and ';

        switch($game['type'])
        {
            case self::TypeParty:
            case self::TypeFighting:
                $message .= 'played a few rounds of ' . $game['name'];
                break;
            case self::TypeRhythm:
                $message .= 'played some ' . $game['name'];
                break;
            case self::TypeBoard:
                $message .= 'played a game of ' . $game['name'];
                break;
            case self::TypeTTRPG:
                $message .= 'played a ' . $game['name'] . ' one-shot';
                break;
            case self::TypeLARPing:
                $message .= 'LARPed in the woods';
                break;
        }

        $message .= '.';

        $lowestPerformer = null;
        $highestPerformer = null;

        if($game['winWith'])
        {
            $petSkills = [];

            foreach($group->getMembers() as $member)
                $petSkills[$member->getId()] = $this->rollSkill($member, $game['winWith']);

            asort($petSkills);
            $lowestPerformer = array_key_first($petSkills);
            $highestPerformer = array_key_last($petSkills);
        }

        foreach($group->getMembers() as $member)
        {
            $messageTemplate = $message;
            $petChanges = new PetChanges($member);

            if($member->getId() === $highestPerformer)
            {
                if($game['type'] === self::TypeBoard)
                    $messageTemplate .= '.. and won!';
                else
                    $messageTemplate .= ' They won most of the games!';

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
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout', 'Gaming Group' ]))
            ;

            if($game['exp'])
                $this->petExperienceService->gainExp($member, 1, $game['exp'], $activityLog);

            if($game['possibleLoot'] && $this->rng->rngNextInt(1, 10) === 1 && !$lowestPerformer)
            {
                $enchantmentName = $this->rng->rngNextFromArray($game['lootEnchantments']);

                $enchantment = $enchantmentName == null ? null : EnchantmentRepository::findOneByName($this->em, $enchantmentName);

                $this->inventoryService->petCollectsEnhancedItem(
                    $this->rng->rngNextFromArray($game['possibleLoot']),
                    $enchantment,
                    null,
                    $member,
                    $this->formatMessage($message, $member, $group) . ' ' . $game['lootMessage'],
                    $activityLog
                );
            }

            $activityLog->setChanges($petChanges->compare($member));
        }

        $this->petRelationshipService->groupGathering(
            $group->getMembers(),
            '%p1% and %p2% talked a little while playing ' . $game['name'] . ' with ' . $group->getName() . '.',
            '%p1% and %p2% avoided talking as much as possible while playing ' . $game['name'] . ' with ' . $group->getName() . '.',
            'Met during a ' . $group->getName() . ' gaming session.',
            '%p1% met %p2% during a ' . $group->getName() . ' gaming session.',
            [ 'Gaming Group' ],
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
