<?php
namespace App\Service\PetActivity\Group;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetGroup;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\GroupNameGenerator;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class SportsBallService
{
    public const ACTIVITY_ICON = 'groups/sportsball';

    private $petExperienceService;
    private $em;
    private $inventoryService;
    private $petRelationshipService;
    private IRandom $squirrel3;
    private GroupNameGenerator $groupNameGenerator;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private ItemRepository $itemRepository;

    public function __construct(
        PetExperienceService $petExperienceService, EntityManagerInterface $em, InventoryService $inventoryService,
        PetRelationshipService $petRelationshipService, Squirrel3 $squirrel3, GroupNameGenerator $groupNameGenerator,
        PetActivityLogTagRepository $petActivityLogTagRepository, ItemRepository $itemRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petRelationshipService = $petRelationshipService;
        $this->squirrel3 = $squirrel3;
        $this->groupNameGenerator = $groupNameGenerator;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->itemRepository = $itemRepository;
    }

    private const DICTIONARY = [
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
            'Gypsies', 'Braves', 'Jesters', 'Viceroys', 'Margraves'
        ],
    ];

    private const GROUP_NAME_PATTERNS = [
        '%adjective% %nouns%',
    ];

    private const POSSIBLE_LOOT = [
        'Green Sportsball Ball',
        'Orange Sportsball Ball',
        'Sportsball Pin',
        'Sportsball Oar',
    ];

    public function generateGroupName(): string
    {
        return $this->groupNameGenerator->generateName(self::GROUP_NAME_PATTERNS, self::DICTIONARY, 60);
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

        return $this->squirrel3->rngNextInt(1, 25 + $total);
    }

    public function meet(PetGroup $group)
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

            $activityLog = (new PetActivityLog())
                ->setPet($member)
                ->setEntry($this->formatMessage($messageTemplate, $member, $group))
                ->setIcon(self::ACTIVITY_ICON)
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Group Hangout', 'Sportsball' ]))
            ;

            $this->petExperienceService->gainExp($member, 1, [
                PetSkillEnum::BRAWL,
                PetSkillEnum::BRAWL,
                PetSkillEnum::STEALTH,
            ], $activityLog);

            if($this->squirrel3->rngNextInt(1, 10) === 1 && $member->getId() !== $lowestPerformer)
            {
                $loot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray(self::POSSIBLE_LOOT));
                $activityLog->setEntry($activityLog->getEntry() . ' ' . $member->getName() . ' accidentally brought ' . $loot->getNameWithArticle() . ' home after the game. (Oops! (Oh well.))');
                $this->inventoryService->petCollectsItem($loot, $member, $this->formatMessage($message, $member, $group), $activityLog);
            }

            $activityLog->setChanges($petChanges->compare($member));

            $this->em->persist($activityLog);
        }

        $this->petRelationshipService->groupGathering(
            $group->getMembers(),
            '%p1% and %p2% talked a little while playing Sportsball with ' . $group->getName() . '.',
            '%p1% and %p2% avoided talking as much as possible while playing Sportsball with ' . $group->getName() . '.',
            'Met during a ' . $group->getName() . ' game.',
            '%p1% met %p2% during a ' . $group->getName() . ' game.',
            [ 'Sportsball' ],
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
