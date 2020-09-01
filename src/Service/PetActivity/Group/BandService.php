<?php
namespace App\Service\PetActivity\Group;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetGroup;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class BandService
{
    private $em;
    private $petRelationshipService;
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $transactionService;

    public function __construct(
        EntityManagerInterface $em, PetRelationshipService $petRelationshipService, InventoryService $inventoryService,
        ResponseService $responseService, PetExperienceService $petExperienceService, TransactionService $transactionService
    )
    {
        $this->em = $em;
        $this->petRelationshipService = $petRelationshipService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
    }

    private const ADJECTIVE_LIST = [
        'Ace', 'Average', 'Above-average', 'Adult', 'Angry', 'Arctic', 'Apologetic', 'Atomic',
        'Born-again', 'Big', 'Baby', 'Brilliant', 'Bad', 'Big City', 'Blue', 'Birthday', 'Bleeding', 'Bubbly',
        'Curious', 'Celestial', 'Careful', 'Chattering', 'Cute',
        'Daft', 'Dapper', 'Deep-sea', 'Deranged', 'Destiny\'s', 'Dry', 'Deep', 'Digital', 'Dirty', 'Doomed',
        'Eternal', 'Evil', 'Electric', 'Easy', 'Emo', 'Empty', 'Excitable',
        'Far-flung', 'Favorite', 'Fabulous', 'Famous', 'Feelgood', 'First', 'Final', 'Fluffy', 'Full-circle', 'Funky',
        'Gentle', 'Gelatinous', 'Giant', 'Golden', 'Good', 'Great', 'Grateful', 'Green', 'Grotesque',
        'Hard', 'Heavy', 'Hungry', 'Happy', 'Harmless', 'Hoof-footed', 'Homemade', 'Humble', 'Hyped',
        'Imaginary', 'Imperfect', 'Ironclad', 'Irresponsible', 'Improved', 'Infamous',
        'Jinxed',
        'Knowledgeable',
        'Last', 'Light-hearted', 'Living', 'Lost', 'Lonely', 'Loud', 'Love-sick', 'Lovely', 'Lucky',
        'Make-shift', 'Missing', 'Modest', 'Morally-ambiguous', 'Mossy', 'Mother\'s', 'Mysterious',
        'Naked', 'Naughty', 'Nasty', 'New', 'Nice', 'Noisy', 'Notorious', 'Nuclear',
        'Odd', 'Outrageous', 'Old',
        'Pretty', 'Primitive', 'Peaceful', 'Perfect', 'Purple', 'Pink', 'Pushy', 'Poor', 'Polite',
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

    private const NOUN_LIST = [
        'Ace', 'Age', 'Act', 'Arcade', 'Alphabet', 'Army', 'Addiction',
        'Baby', 'Blade', 'Boulder', 'Bank', 'Blossom', 'Bookkeeper', 'Bumblebee', 'Butter',
        'Castle', 'Cat', 'Cereal', 'Chain', 'Chemical', 'Circle', 'Circus', 'Clown', 'Country', 'Crawlspace', 'Cream', 'Cure',
        'Calculator', 'Coincidence', 'Collective', 'Council', 'Crew', 'Cataclysm',
        'Dead', 'Deviation', 'Dimension', 'Diver', 'Doctor', 'Dog', 'Doom', 'Door', 'Dragon',
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

    private const PLURAL_NOUN_LIST = [
        'Aliens', 'Accords', 'Acts', 'Ashes', 'Arms and Legs', 'Acres', 'Armies',
        'Bananas', 'Boys and Girls', 'Blades', 'Blossoms',
        'Children', 'Chemicals', 'Chains', 'Circles', 'Clowns', 'Cups', 'Circles', 'Countries', 'Clues', 'Crawlies',
        'Dancers', 'Depths', 'Devils', 'Dinosaurs', 'Doctors', 'Dogs and Cats', 'Dollars', 'Doors', 'Divers',
        'Embers', 'Explorers',
        'Faces', 'Favorites', 'Fighters', 'Fires', 'Fireflies', 'Foreigners',
        'Gardens', 'Giants', 'Gorillas', 'Grasses', 'Guns', 'Guardians',
        'Haircuts', 'Hearts', 'Hermits', 'Heartstrings',
        'Jokers',
        'Kings', 'Killers', 'Kingdoms', 'Kittens', 'Knights',
        'Laser Beams', 'Lines', 'Lips', 'Tongues',
        'Machines', 'Magpies', 'Mathematicians', 'Misfits', 'Monuments', 'Mountains',
        'Nachos', 'Nations',
        'Oceans', 'Obsessions',
        'People', 'Planets', 'Pieces', 'Parents', 'Pathways', 'Patriots', 'Peppers',
        'Queens',
        'Rascals', 'Rebels', 'Robots', 'Roses', 'Royalty',
        'Steps', 'Stars', 'Seas', 'Suns', 'Sons and Daughters', 'Shapes', 'Sins', 'Stones',
        'Toys', 'Things', 'Tones', 'Travelers', 'Troubles',
        'Vibrations', 'Visions', 'Voices',
        'Wars', 'Words', 'Winds', 'Walls', 'Wallflowers',
    ];

    private const NUMBER_LIST = [
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

    public function generateBandName(): string
    {
        $pattern = ArrayFunctions::pick_one([
            'The? %noun% %nouns%',
            'The/My/Your/Our? %adjective%? %noun% %nouns%',
            'The? %adjective%? %noun% %nouns%',
            'From/Of/For? %adjective% %nouns%',
            'The/One? %adjective% %noun%',
            'The? %adjective% %nouns%/%noun%',
            'The? %adjective% %nouns%/%noun%',

            'The %adjective% and the %adjective%',
            'The %adjective% and the %adjective%',

            'The %noun% , the %noun% ,/,_and the %noun%',

            '%number% of_the? %nouns%',
            '%nouns% of/and/from/over/vs/with/without %nouns%',
            'The? %noun% from/of the %nouns%/%noun%',
            '%adjective% and %adjective%',
        ]);

        $parts = explode(' ', $pattern);
        $newParts = [];
        foreach($parts as $part)
        {
            if($part[strlen($part) - 1] === '?')
            {
                if(mt_rand(1, 2) === 1)
                    $part = substr($part, 0, strlen($part) - 1);
                else
                    continue;
            }

            if(strpos($part, '/') !== false)
                $part = ArrayFunctions::pick_one(explode('/', $part));

            if($part === '%noun%')
                $newParts[] = ArrayFunctions::pick_one(self::NOUN_LIST);
            else if($part === '%nouns%')
                $newParts[] = ArrayFunctions::pick_one(self::PLURAL_NOUN_LIST);
            else if($part === '%adjective%')
                $newParts[] = ArrayFunctions::pick_one(self::ADJECTIVE_LIST);
            else if($part === '%number%')
                $newParts[] = ArrayFunctions::pick_one(self::NUMBER_LIST);
            else
                $newParts[] = $part;
        }

        return str_replace(['_', ' ,'], [' ', ','], implode(' ', $newParts));
    }

    private const BAND_ACTIVITY_SENTIMENT_MESSAGES = [
        'It was fun!',
        'It was a good session!',
        'It was a little stressful, but they made good progress!',
    ];

    public function meet(PetGroup $group)
    {
        if($group->getNumberOfProducts() > 0 && mt_rand(1, 10) === 1)
        {
            $r = mt_rand(1, 100);

            if ($r <= 75)
                $this->receiveFanMail($group);
            else //if ($r <= 75)
                $this->receiveRoyalties($group);
            // TODO:
            //else
            //    $this->receiveRandomItem($instigatingPet, $group);
        }
        else
            $this->produceAlbum($group);

        $group->setLastMetOn();
    }

    private const FAN_MAIL_FEELS = [
        'delighted!', 'touched.', 'very proud.',
        'ecstatic!', 'moved.'
    ];

    public function receiveFanMail(PetGroup $group)
    {
        foreach($group->getMembers() as $pet)
        {
            $changes = new PetChanges($pet);

            $feels = self::FAN_MAIL_FEELS[($pet->getId() * 89) % count(self::FAN_MAIL_FEELS)];

            $pet
                ->increaseEsteem(mt_rand(6, 12))
                ->increaseLove(mt_rand(2, 4))
            ;

            $activityLog = (new PetActivityLog())
                ->setPet($pet)
                ->setEntry($group->getName() . ' received some fan mail! ' . $pet->getName() . ' was ' . $feels)
                ->setIcon('items/music/note')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->setChanges($changes->compare($pet))
            ;

            $this->em->persist($activityLog);
        }
    }

    public function receiveRoyalties(PetGroup $group)
    {
        $moneys = mt_rand(1, 3) + floor(
            sqrt($group->getNumberOfProducts() * 10) / count($group->getMembers())
        );

        foreach($group->getMembers() as $pet)
        {
            $changes = new PetChanges($pet);

            $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' got royalties from ' . $group->getName() . ' sales!');

            $pet->increaseEsteem(mt_rand(4, 8));

            $activityLog = (new PetActivityLog())
                ->setPet($pet)
                ->setEntry($pet->getName() . ' got royalties from ' . $group->getName() . ' sales!')
                ->setIcon('items/music/note')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->setChanges($changes->compare($pet))
            ;

            $this->em->persist($activityLog);
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function produceAlbum(PetGroup $group)
    {
        $bandSize = count($group->getMembers());

        $soothingVoiceValue = 3;
        $skill = 0;
        $progress = mt_rand(5, 12 + $bandSize * 2);
        /** @var PetChanges[] $petChanges */ $petChanges = [];

        foreach($group->getMembers() as $pet)
        {
            $petChanges[$pet->getId()] = new PetChanges($pet);

            $roll = mt_rand(1, 10 + $pet->getMusic());

            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $roll += $soothingVoiceValue;

                if($soothingVoiceValue > 0) $soothingVoiceValue--;
            }

            $this->petExperienceService->gainExp($pet, max(1, floor($roll / 5)), [ PetSkillEnum::MUSIC ]);

            $skill += $roll;
        }

        $group
            ->increaseProgress($progress)
            ->increaseSkillRollTotal($skill)
        ;

        if($group->getProgress() >= 100)
        {
            $totalRoll = mt_rand(1, $group->getSkillRollTotal());

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

            // @TODO:
            /*
            else //if($totalRoll < 400)
                $item = '???';
            */

            foreach($group->getMembers() as $member)
            {
                $member->increaseEsteem(mt_rand(8, 12));

                $activityLog = (new PetActivityLog())
                    ->setPet($member)
                    ->setEntry($group->getName() . (mt_rand(1, 5) === 1 ? ' finally' : '') . ' released a new ' . $item . '!')
                    ->setIcon('items/music/note')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                ;

                $this->em->persist($activityLog);

                $this->inventoryService->petCollectsItem($item, $member, $member->getName() . '\'s band made this!', $activityLog);
            }
        }
        else
        {
            $groupSentiment = ArrayFunctions::pick_one([ 0, 0, 1, 1, 1, 2 ]);

            foreach($group->getMembers() as $member)
            {
                if(mt_rand(1, 8) === 1)
                    $sentiment = ArrayFunctions::pick_one([ 0, 0, 1, 1, 1, 2 ]);
                else
                    $sentiment = $groupSentiment;

                if($sentiment === 0)
                    $member->increaseLove(mt_rand(2, 6));
                else if($sentiment === 1)
                    $member->increaseEsteem(mt_rand(2, 6));

                $activityLog = (new PetActivityLog())
                    ->setPet($member)
                    ->setEntry($member->getName() . ' jammed with ' . $group->getName() . '. ' . self::BAND_ACTIVITY_SENTIMENT_MESSAGES[$sentiment])
                    ->setIcon('items/music/note')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                ;

                $this->em->persist($activityLog);
            }
        }

        $this->petRelationshipService->groupGathering(
            $group->getMembers(),
            '%p1% and %p2% talked a little while playing together for ' . $group->getName() . '.',
            '%p1% and %p2% avoided talking as much as possible while playing together for ' . $group->getName() . '.',
            'Met during a ' . $group->getName() . ' jam session.',
            '%p1% met %p2% during a ' . $group->getName() . ' jam session.',
            100
        );
    }
}
