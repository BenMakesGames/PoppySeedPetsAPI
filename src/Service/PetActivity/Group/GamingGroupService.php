<?php
declare(strict_types=1);

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
    public const ActivityIcon = 'groups/gaming';

    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly PetRelationshipService $petRelationshipService,
        private readonly IRandom $squirrel3
    )
    {
    }

    private const DICTIONARY = [
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

    private const GROUP_NAME_PATTERNS = [
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
        return GroupNameGenerator::generateName($this->squirrel3, self::GROUP_NAME_PATTERNS, self::DICTIONARY, 60);
    }

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

        return $this->squirrel3->rngNextInt(1, 20 + $total);
    }

    private const NAME_SCRAWLFUL_2 = 'Scrawlful 2';

    private const TYPE_FIGHTING = 'fighting';
    private const TYPE_RHYTHM = 'rhythm';
    private const TYPE_BOARD = 'board';
    private const TYPE_PARTY = 'party';
    private const TYPE_TTRPG = 'TTRPG';
    private const TYPE_LARPING = 'LARPing';

    public function meet(PetGroup $group)
    {
        $partyGameName = $this->squirrel3->rngNextFromArray([ 'Reds to Reds', self::NAME_SCRAWLFUL_2, 'Mixit', 'One-night Werecreature' ]);

        $game = $this->squirrel3->rngNextFromArray([
            [
                'type' => self::TYPE_FIGHTING,
                'name' => $this->squirrel3->rngNextFromArray([ 'Hyper Smash Sisters', 'Spiritcalibur II' ]),
                'winWith' => [ 'dexterity', 'perception', 'intelligence' ],
                'exp' => null,
                'possibleLoot' => null,
                'lootMessage' => null,
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TYPE_RHYTHM,
                'name' => $this->squirrel3->rngNextFromArray([ 'Dance Dance Uprising', 'Guitar Champion' ]),
                'winWith' => null,
                'exp' => [ PetSkillEnum::MUSIC ],
                'possibleLoot' => [ 'Music Note' ],
                'lootMessage' => 'They played through a ton of songs!',
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TYPE_BOARD,
                'name' => $this->squirrel3->rngNextFromArray([ 'Settlers of Hollow Earth', 'Galaxy Hauler' ]),
                'winWith' => [ 'luck' ],
                'exp' => null,
                'possibleLoot' => [ 'Glowing Six-sided Die' ],
                'lootMessage' => 'Somehow they ended up with more dice than they started with...',
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TYPE_BOARD,
                'name' => $this->squirrel3->rngNextFromArray([ 'Naner Farmer', 'Spice Magnate', 'Terraforming Ganymede', 'Gemstone Alchemist' ]),
                'winWith' => [ 'intelligence' ],
                'exp' => null,
                'possibleLoot' => null,
                'lootMessage' => null,
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TYPE_PARTY,
                'name' => $partyGameName,
                'winWith' => [ 'extroversion', 'luck' ],
                'exp' => ($partyGameName === self::NAME_SCRAWLFUL_2) ? [ PetSkillEnum::CRAFTS ] : null,
                'possibleLoot' => null,
                'lootMessage' => null,
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TYPE_TTRPG,
                'name' => 'Keeps and Colossi',
                'winWith' => null,
                'exp' => null,
                'possibleLoot' => [ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ],
                'lootMessage' => 'Somehow they ended up with more dice than they started with...',
                'lootEnchantments' => [ null ],
            ],
            [
                'type' => self::TYPE_LARPING,
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
            case self::TYPE_PARTY:
            case self::TYPE_FIGHTING:
                $message .= 'played a few rounds of ' . $game['name'];
                break;
            case self::TYPE_RHYTHM:
                $message .= 'played some ' . $game['name'];
                break;
            case self::TYPE_BOARD:
                $message .= 'played a game of ' . $game['name'];
                break;
            case self::TYPE_TTRPG:
                $message .= 'played a ' . $game['name'] . ' one-shot';
                break;
            case self::TYPE_LARPING:
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
                if($game['type'] === self::TYPE_BOARD)
                    $messageTemplate .= '.. and won!';
                else
                    $messageTemplate .= ' They won most of the games!';

                $member->increaseEsteem($this->squirrel3->rngNextInt(4, 7));
            }
            else if($member->getId() === $lowestPerformer)
            {
                if($member->getEsteem() < 0)
                    $messageTemplate .= ' They didn\'t do very well...';
                else
                {
                    $messageTemplate .= ' They didn\'t do very well, but it was still fun.';
                    $member->increaseEsteem($this->squirrel3->rngNextInt(2, 5));
                }
            }
            else
                $member->increaseEsteem($this->squirrel3->rngNextInt(3, 6));

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $member, $this->formatMessage($messageTemplate, $member, $group))
                ->setIcon(self::ActivityIcon)
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout', 'Gaming Group' ]))
            ;

            if($game['exp'])
                $this->petExperienceService->gainExp($member, 1, $game['exp'], $activityLog);

            if($game['possibleLoot'] && $this->squirrel3->rngNextInt(1, 10) === 1 && !$lowestPerformer)
            {
                $enchantmentName = $this->squirrel3->rngNextFromArray($game['lootEnchantments']);

                $enchantment = $enchantmentName == null ? null : EnchantmentRepository::findOneByName($this->em, $enchantmentName);

                $this->inventoryService->petCollectsEnhancedItem(
                    $this->squirrel3->rngNextFromArray($game['possibleLoot']),
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

    private function formatMessage(string $template, Pet $member, PetGroup $group)
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
