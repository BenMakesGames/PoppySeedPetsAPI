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

use App\Entity\PetGroup;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetSkillEnum;
use App\Functions\GroupNameGenerator;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class BandService
{
    public const string ActivityIcon = 'groups/band';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PetRelationshipService $petRelationshipService,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly TransactionService $transactionService,
        private readonly IRandom $rng
    )
    {
    }

    private const array AdjectiveList = [
        'Ace', 'Average', 'Above-average', 'Adult', 'Angry', 'Arctic', 'Apologetic', 'Atomic',
        'Born-again', 'Big', 'Baby', 'Brilliant', 'Bad', 'Big City', 'Blue', 'Bold', 'Birthday', 'Bleeding', 'Bubbly',
        'Curious', 'Celestial', 'Careful', 'Chattering', 'Cute',
        'Daft', 'Dapper', 'Deep-sea', 'Deranged', 'Destiny\'s', 'Dry', 'Deep', 'Digital', 'Dirty', 'Doomed',
        'Eternal', 'Evil', 'Electric', 'Easy', 'Emo', 'Empty', 'Excitable',
        'Far-flung', 'Favorite', 'Fabulous', 'Famous', 'Feelgood', 'First', 'Final', 'Flourishing', 'Fluffy', 'Full-circle', 'Funky',
        'Gentle', 'Gelatinous', 'Giant', 'Golden', 'Good', 'Great', 'Grateful', 'Green', 'Grotesque',
        'Hard', 'Heavy', 'Hungry', 'Happy', 'Harmless', 'Hoof-footed', 'Homemade', 'Humble', 'Hyped',
        'Imaginary', 'Imperfect', 'Ironclad', 'Irresponsible', 'Improved', 'Infamous',
        'Jinxed',
        'Knowledgeable', 'Kung-fu',
        'Last', 'Lazy', 'Light-hearted', 'Living', 'Lost', 'Lonely', 'Loud', 'Love-sick', 'Lovely', 'Lucky',
        'Make-shift', 'Missing', 'Modest', 'Morally-ambiguous', 'Mossy', 'Mother\'s', 'Mysterious',
        'Naked', 'Naughty', 'Nasty', 'New', 'Nice', 'Noisy', 'Notorious', 'Nuclear',
        'Odd', 'Outrageous', 'Old',
        'Pretty', 'Primitive', 'Peaceful', 'Peculiar', 'Perfect', 'Purple', 'Pink', 'Pushy', 'Poor', 'Polite',
        'Questionable', 'Quiet', 'Quibbling',
        'Rancid', 'Raucous', 'Red', 'Red-hot', 'Rich', 'Riverside', 'Rolling', 'Romantic', 'Round', 'Rose-colored', 'Rude',
        'Rundown',
        'Sappho\'s', 'Savage', 'Second', 'Short', 'Silver', 'Simple', 'Single-minded', 'Sinning', 'Smallish', 'Small Town', 'Smashing', 'Sorry',
        'Star-crossed', 'Shakespearean', 'Sonic', 'Soft', 'Sleepy', 'Spicy', 'Strange', 'Sweet',
        'Third', 'Three-sided', 'Time-traveling', 'Tropical', 'Tricky', 'Toothsome',
        'Unknown', 'Unwilling', 'Unapologetic', 'Unjust', 'Unhealthy', 'Undecided',
        'Victorious', 'Visionary', 'Violent',
        'Willing', 'Wandering', 'Wonderful', 'Wet', 'Weird', 'Wavering',
    ];

    private const array NounList = [
        'Ace', 'Age', 'Act', 'Arcade', 'Alphabet', 'Army', 'Addiction',
        'Baby', 'Blade', 'Boulder', 'Bank', 'Blossom', 'Bookkeeper', 'Bumblebee', 'Butter',
        'Castle', 'Cat', 'Cereal', 'Chain', 'Chemical', 'Circle', 'Circus', 'Clown', 'Country', 'Crawlspace', 'Cream', 'Cure',
        'Calculator', 'Coincidence', 'Collective', 'Council', 'Crew', 'Cataclysm',
        'Dead', 'Delegation', 'Deviation', 'Dimension', 'Diver', 'Doctor', 'Dog', 'Doom', 'Door', 'Dragon',
        'Eye',
        'Factory', 'Festival', 'Fire', 'Fighter', 'Forest', 'Foreigner',
        'Glacier', 'Gargoyle', 'Giant', 'Garden', 'Gluestick', 'Goldfish', 'Grass', 'Guardian',
        'Haircut', 'Heart', 'Heartbreak', 'Heartstring', 'Honeydont',
        'Jar', 'Joker', 'Jigsaw',
        'King', 'Kitten', 'Knight',
        'Laser Beam', 'Line', 'League',
        'Magic', 'Machine', 'Melting-point', 'Monument', 'Moon', 'Mountain', 'Monologue', 'Mouth', 'Mushroom', 'Mystery',
        'Newbie', 'Nation', 'Nightmare', 'Night', 'Ninja',
        'Oasis', 'Obsession', 'Offspring',
        'Parade', 'Party', 'Pattern', 'Palace', 'Pizza', 'Placebo', 'Planet', 'Play', 'Poodle',
        'Queen',
        'Rose', 'Rock', 'Romance',
        'Six', 'Seven', 'Startled', 'Story', 'System', 'Soul', 'Sword', 'Symmetry',
        'Telephone', 'Theory', 'Tesseract', 'Tongue', 'Tool', 'Traveler', 'Tree', 'Triangle',
        'Unicorn', 'UFO',
        'Vanilla', 'Viceroy', 'Violence', 'Voice',
        'War', 'Wallflower', 'Wind', 'Window', 'Wizard',
    ];

    private const array PluralNounList = [
        'Aliens', 'Accords', 'Acts', 'Ashes', 'Arms and Legs', 'Acres', 'Armies',
        'Bananas', 'Boys and Girls', 'Blades', 'Blossoms',
        'Children', 'Chemicals', 'Chains', 'Circles', 'Clowns', 'Cups', 'Countries', 'Clues', 'Crawlies',
        'Dancers', 'Depths', 'Devils', 'Dinosaurs', 'Doctors', 'Dogs and Cats', 'Dollars', 'Doors', 'Divers',
        'Embers', 'Explorers',
        'Faces', 'Favorites', 'Fighters', 'Fires', 'Fireflies', 'Foreigners',
        'Gardens', 'Giants', 'Gorillas', 'Grasses', 'Guns', 'Guardians',
        'Haircuts', 'Hearts', 'Hermits', 'Heartstrings', 'Hoodlums',
        'Jokers',
        'Kings', 'Killers', 'Kingdoms', 'Kittens', 'Knights',
        'Laser Beams', 'Lines', 'Lips', 'Tongues',
        'Machines', 'Magpies', 'Mathematicians', 'Misfits', 'Monuments', 'Mountains',
        'Nachos', 'Nations',
        'Oceans', 'Obsessions',
        'People', 'Planets', 'Pieces', 'Parents', 'Pathways', 'Patriots', 'Peppers',
        'Queens',
        'Rascals', 'Rebels', 'Robots', 'Roses', 'Royalty',
        'Shapes', 'Steps', 'Stars', 'Seas', 'Suns', 'Sons and Daughters', 'Shapes', 'Sins', 'Stones',
        'Toys', 'Things', 'Tones', 'Travelers', 'Troubles',
        'Vibrations', 'Visions', 'Voices',
        'Wars', 'Words', 'Winds', 'Walls', 'Wallflowers',
    ];

    private const array NumberList = [
        'Two',
        '7',
        '33',
        'Forty',
        '99',
        '100',
        '360',
        '1337',
        '10,000',
        'Few',
        'Some',
        'Most',
        'All',
    ];

    private const array GroupNamePatterns = [
        'the? %noun% %nouns%',
        'the/my/your/our? %adjective%? %noun% %nouns%',
        'the? %adjective%? %noun% %nouns%',
        'from/of/for? %adjective% %nouns%',
        'the/one? %adjective% %noun%',
        'the? %adjective% %nouns%/%noun%',
        'the? %adjective% %nouns%/%noun%',

        'the %adjective% and the %adjective%',
        'the %adjective% and the %adjective%',

        'the %noun% , the %noun% ,/,_and the %noun%',

        '%number% of_the? %nouns%',
        '%nouns% of/and/from/over/vs/with/without %nouns%',
        'the? %noun% from/of the %nouns%/%noun%',
        '%adjective% and %adjective%',
    ];

    private const array Dictionary = [
        'noun' => self::NounList,
        'nouns' => self::PluralNounList,
        'adjective' => self::AdjectiveList,
        'number' => self::NumberList,
    ];

    public function generateGroupName(): string
    {
        return GroupNameGenerator::generateName($this->rng, self::GroupNamePatterns, self::Dictionary, 60);
    }

    private const array BandActivitySentimentMessages = [
        'It was fun!',
        'It was a good session!',
        'It was a little stressful, but they made good progress!',
    ];

    public function meet(PetGroup $group): void
    {
        if($group->getNumberOfProducts() > 0 && $this->rng->rngNextInt(1, 10) === 1)
        {
            $r = $this->rng->rngNextInt(1, 100);

            if ($r <= 75)
                $this->receiveFanMail($group);
            else //if ($r <= 75)
                $this->receiveRoyalties($group);
        }
        else
            $this->produceAlbum($group);

        $group->setLastMetOn();
    }

    private const array FanMailFeels = [
        'delighted!', 'touched.', 'very proud.',
        'ecstatic!', 'moved.'
    ];

    public function receiveFanMail(PetGroup $group): void
    {
        foreach($group->getMembers() as $pet)
        {
            $changes = new PetChanges($pet);

            $feels = self::FanMailFeels[($pet->getId() * 89) % count(self::FanMailFeels)];

            $pet
                ->increaseEsteem($this->rng->rngNextInt(6, 12))
                ->increaseLove($this->rng->rngNextInt(2, 4))
            ;

            PetActivityLogFactory::createUnreadLog($this->em, $pet, $group->getName() . ' received some fan mail! %pet:' . $pet->getId() . '.name% was ' . $feels)
                ->setIcon(self::ActivityIcon)
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->setChanges($changes->compare($pet))
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout', 'Band' ]))
            ;
        }
    }

    public function receiveRoyalties(PetGroup $group): void
    {
        $moneys = $this->rng->rngNextInt(1, 3) + (int)floor(
            sqrt($group->getNumberOfProducts() * 10) / count($group->getMembers())
        );

        foreach($group->getMembers() as $pet)
        {
            $changes = new PetChanges($pet);

            $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' got royalties from ' . $group->getName() . ' sales!');

            $pet->increaseEsteem($this->rng->rngNextInt(4, 8));

            PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% got royalties from ' . $group->getName() . ' sales!')
                ->setIcon(self::ActivityIcon)
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->setChanges($changes->compare($pet))
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout', 'Band', 'Moneys' ]))
            ;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function produceAlbum(PetGroup $group): void
    {
        $activityLogsPerPet = [];
        $expGainPerPet = [];
        $bandSize = count($group->getMembers());

        $soothingVoiceValue = 3;
        $skill = 0;
        $progress = $this->rng->rngNextInt(5, 12 + $bandSize * 2);
        /** @var PetChanges[] $petChanges */ $petChanges = [];

        foreach($group->getMembers() as $pet)
        {
            $petWithSkills = $pet->getComputedSkills();
            $petChanges[$pet->getId()] = new PetChanges($pet);

            $roll = $this->rng->rngNextInt(1, 10 + $petWithSkills->getMusic()->getTotal());

            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE) && $soothingVoiceValue > 0)
            {
                $roll += $soothingVoiceValue;
                $soothingVoiceValue--;
            }

            $expGainPerPet[$pet->getId()] = max(1, (int)floor($roll / 5));

            $skill += $roll;
        }

        $group
            ->increaseProgress($progress)
            ->increaseSkillRollTotal($skill)
        ;

        if($group->getProgress() >= 100)
        {
            $totalRoll = $this->rng->rngNextInt(1, $group->getSkillRollTotal());

            $group
                ->clearProgress()
                ->increaseNumberOfProducts()
            ;

            if($totalRoll < 100)
                $item = 'Single';
            else if($totalRoll < 200)
                $item = 'EP';
            else //if($totalRoll < 300)
                $item = 'LP';

            foreach($group->getMembers() as $member)
            {
                $member->increaseEsteem($this->rng->rngNextInt(8, 12));

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $member, $group->getName() . ($this->rng->rngNextInt(1, 5) === 1 ? ' finally' : '') . ' released a new ' . $item . '!')
                    ->setIcon(self::ActivityIcon)
                    ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout', 'Band' ]))
                ;

                $activityLogsPerPet[$member->getId()] = $activityLog;

                $this->inventoryService->petCollectsItem($item, $member, $member->getName() . '\'s band made this!', $activityLog);
            }
        }
        else
        {
            $groupSentiment = $this->rng->rngNextFromArray([ 0, 0, 1, 1, 1, 2 ]);

            foreach($group->getMembers() as $member)
            {
                if($this->rng->rngNextInt(1, 8) === 1)
                    $sentiment = $this->rng->rngNextFromArray([ 0, 0, 1, 1, 1, 2 ]);
                else
                    $sentiment = $groupSentiment;

                if($sentiment === 0)
                    $member->increaseLove($this->rng->rngNextInt(2, 6));
                else if($sentiment === 1)
                    $member->increaseEsteem($this->rng->rngNextInt(2, 6));

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $member, $member->getName() . ' jammed with ' . $group->getName() . '. ' . self::BandActivitySentimentMessages[$sentiment])
                    ->setIcon(self::ActivityIcon)
                    ->addInterestingness(PetActivityLogInterestingness::HoHum)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout', 'Band' ]))
                ;

                $activityLogsPerPet[$member->getId()] = $activityLog;
            }
        }

        foreach($group->getMembers() as $pet)
            $this->petExperienceService->gainExp($pet, $expGainPerPet[$pet->getId()], [ PetSkillEnum::Music ], $activityLogsPerPet[$pet->getId()]);

        $this->petRelationshipService->groupGathering(
            $group->getMembers(),
            '%p1% and %p2% talked a little while playing together for ' . $group->getName() . '.',
            '%p1% and %p2% avoided talking as much as possible while playing together for ' . $group->getName() . '.',
            'Met during a ' . $group->getName() . ' jam session.',
            '%p1% met %p2% during a ' . $group->getName() . ' jam session.',
            [ 'Band' ],
            100
        );
    }
}
