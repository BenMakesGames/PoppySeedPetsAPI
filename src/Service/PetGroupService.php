<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetGroup;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetGroupTypeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetGroupService
{
    private $em;
    private $petRepository;
    private $responseService;
    private $petExperienceService;
    private $inventoryService;
    private $petRelationshipService;

    public function __construct(
        EntityManagerInterface $em, PetRepository $petRepository, ResponseService $responseService,
        PetExperienceService $petExperienceService, InventoryService $inventoryService,
        PetRelationshipService $petRelationshipService
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->petRelationshipService = $petRelationshipService;
    }

    public function doGroupActivity(Pet $instigatingPet, PetGroup $group)
    {
        // @TODO if two of the pets dislike each other, or broke up, then one of the two leaves.
        // there's a chance that this breaks up the group entirely. if this puts the number of members
        // below minimum, then it definitely breaks the group up unless they can find a replacement

        switch ($group->getType())
        {
            case PetGroupTypeEnum::BAND:
                $this->takesTime($instigatingPet, $group, PetActivityStatEnum::GROUP_BAND);
                $this->doBandActivity($instigatingPet, $group);
                break;

            default:
                throw new \Exception('Unhandled group type "' . $group->getType() . '"');
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function takesTime(Pet $instigatingPet, PetGroup $group, string $petActivity)
    {
        foreach($group->getMembers() as $member)
        {
            $time = ($member->getId() === $instigatingPet->getId()) ? mt_rand(45, 60) : 5;

            $this->petExperienceService->spendTime($member, $time, $petActivity, true);
        }
    }

    public function createGroup(Pet $pet): ?PetGroup
    {
        $availableFriends = $this->petRepository->findFriendsWithFewGroups($pet);

        if(count($availableFriends) < 2)
            return null;

        // @TODO: when we have more than one group type, we'll have to pick one here
        $type = PetGroupTypeEnum::BAND;

        $group = (new PetGroup())
            ->setType($type)
            ->setName($this->generateName($type))
        ;

        $this->em->persist($group);

        $pet->addGroup($group);

        switch($type)
        {
            case PetGroupTypeEnum::BAND:
                usort($availableFriends, function (Pet $a, Pet $b) {
                    return $b->getMusic() <=> $a->getMusic();
                });
                break;

            default:
                shuffle($availableFriends);
        }

        $friendNames = [
            $availableFriends[0]->getName(),
            $availableFriends[1]->getName(),
        ];

        $availableFriends[0]->addGroup($group);
        $availableFriends[1]->addGroup($group);

        $this->petExperienceService->spendTime($availableFriends[0], 5, PetActivityStatEnum::HANG_OUT, true);
        $this->petExperienceService->spendTime($availableFriends[1], 5, PetActivityStatEnum::HANG_OUT, true);

        if(count($availableFriends) >= 3 && mt_rand(1, 2) === 1)
        {
            $availableFriends[2]->addGroup($group);
            $this->petExperienceService->spendTime($availableFriends[2], 5, PetActivityStatEnum::HANG_OUT, true);
            $friendNames[] = $availableFriends[2]->getName();
        }

        if(count($availableFriends) >= 4 && mt_rand(1, 2) === 1)
        {
            $availableFriends[3]->addGroup($group);
            $this->petExperienceService->spendTime($availableFriends[3], 5, PetActivityStatEnum::HANG_OUT, true);
            $friendNames[] = $availableFriends[3]->getName();
        }

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HANG_OUT, true);

        $this->responseService->createActivityLog($pet, $pet->getName() . ' started a new band with ' . ArrayFunctions::list_nice($friendNames) . '.', 'items/music/note');

        return $group;
    }

    private function generateName(int $type): string
    {
        switch($type)
        {
            case PetGroupTypeEnum::BAND:
                return $this->generateBandName();
            default:
                throw new \Exception('Ben forgot to program group names for groups of type "' . $type . '"! (Bad Ben!)');
        }
    }

    private const ADJECTIVE_LIST = [
        'Ace', 'Average', 'Above-average', 'Adult', 'Angry', 'Arctic', 'Apologetic',
        'Born-again', 'Big', 'Baby', 'Brilliant', 'Bad', 'Blue', 'Birthday',
        'Curious', 'Celestial', 'Careful', 'Chattering',
        'Deranged', 'Dapper', 'Destiny\'s', 'Dry', 'Deep', 'Digital', 'Deep-sea',
        'Eternal', 'Evil', 'Electric', 'Easy', 'Empty',
        'Full-circle', 'Far-flung', 'First', 'Final', 'Favorite', 'Fabulous', 'Feelgood', 'Famous',
        'Grotesque', 'Gentle', 'Giant', 'Good', 'Great', 'Grateful',
        'Heavy', 'Hungry', 'Happy', 'Harmless', 'Hoof-footed', 'Hard', 'Humble',
        'Imaginary', 'Ironclad', 'Improved', 'Infamous',
        'Jinxed',
        'Knowledgeable',
        'Lost', 'Lonely', 'Living', 'Loud', 'Last', 'Love-sick', 'Lovely', 'Light-hearted',
        'Make-shift', 'Mysterious', 'Mossy', 'Mother\'s',
        'Naked', 'Naughty', 'Nice', 'New', 'Nuclear', 'Noisy',
        'Odd', 'Outrageous', 'Old',
        'Pretty', 'Primitive', 'Peaceful', 'Purple', 'Pink', 'Pushy', 'Poor', 'Polite',
        'Quiet', 'Quibbling',
        'Red', 'Run-down', 'Rich', 'Rose-colored', 'Raucous', 'Rude',
        'Strange', 'Silver', 'Sorry', 'Second', 'Star-crossed', 'Shakespearean', 'Sonic', 'Soft', 'Short', 'Single-minded',
        'Third', 'Tropical', 'Tricky', 'Toothsome',
        'Unknown', 'Unwilling', 'Unapologetic', 'Unjust', 'Unhealthy', 'Undecided',
        'Victorious',
        'Willing', 'Wandering', 'Wonderful', 'Wet', 'Wavering',
    ];

    private const NOUN_LIST = [
        'Ace', 'Age', 'Act', 'Arcade', 'Alphabet', 'Army',
        'Baby', 'Blade', 'Boulder', 'Bank', 'Bookkeeper',
        'Circus', 'Country', 'Castle', 'Cream', 'Cat', 'Cure', 'Coincidence', 'Calculator', 'Council', 'Crew', 'Cataclysm',
        'Door', 'Deviation', 'Doctor', 'Dog', 'Diver', 'Dragon',
        'Forest', 'Festival', 'Fire',
        'Glacier', 'Gargoyle', 'Giant', 'Garden', 'Grass', 'Guardian',
        'Heart', 'Heartbreak', 'Heartstring',
        'Joker', 'Jigsaw',
        'King', 'Knight',
        'Laser Beam', 'Line', 'League',
        'Melting-point', 'Mystery', 'Moon', 'Mountain', 'Machine', 'Monologue',
        'Night', 'Newbie', 'Nation', 'Nightmare',
        'Obsession',
        'Parade', 'Planet', 'Play', 'Party', 'Pizza', 'Pattern', 'Palace',
        'Queen',
        'Rose', 'Rock',
        'Sword', 'Story', 'System', 'Soul',
        'Tool', 'Triangle', 'Tesseract', 'Tongue', 'Theory', 'Tree', 'Telephone',
        'Unicorn', 'UFO',
        'Voice', 'Viceroy',
        'War', 'Wind', 'Window',
    ];

    private const PLURAL_NOUN_LIST = [
        'Aliens', 'Accords', 'Acts', 'Ashes', 'Arms and Legs', 'Acres', 'Armies',
        'Bananas', 'Boys and Girls', 'Blades',
        'Cups', 'Circles', 'Dogs and Cats', 'Countries', 'Clues', 'Children',
        'Dinosaurs', 'Doors', 'Devils', 'Dancers', 'Doctors', 'Dollars', 'Depths', 'Divers',
        'Embers', 'Explorers',
        'Fires', 'Faces', 'Favorites',
        'Giants', 'Gardens', 'Grasses', 'Guns', 'Guardians',
        'Hearts', 'Hermits', 'Heartstrings',
        'Jokers',
        'Kings', 'Killers', 'Kingdoms', 'Knights',
        'Laser Beams', 'Lines', 'Lips', 'Tongues',
        'Misfits', 'Mountains',
        'Nachos', 'Nations',
        'Oceans', 'Obsessions',
        'People', 'Planets', 'Pieces', 'Parents', 'Pathways', 'Patriots',
        'Queens',
        'Robots', 'Rascals', 'Roses', 'Rebels',
        'Steps', 'Stars', 'Seas', 'Suns', 'Sons and Daughters', 'Shapes',
        'Toys', 'Things', 'Tones', 'Troubles',
        'Voices', 'Vibrations',
        'Wars', 'Words', 'Winds', 'Walls',
    ];

    private const NUMBER_LIST = [
        'Two',
        '7',
        '33',
        'Forty',
        '99',
        '100',
        '360',
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
            'The? %adjective%? %noun% %nouns%',
            'The? %adjective%? %noun% %nouns%',
            'From/Of/For? %adjective% %nouns%',
            'The/One? %adjective% %noun%',
            'The? %adjective% %nouns%/%noun%',
            'The? %adjective% %nouns%/%noun%',

            'The %adjective% and the %adjective%',
            'The %adjective% and the %adjective%',

            'The %noun% , the %noun% ,/,_and the %noun%',

            '%number% of_the? %nouns%',
            '%nouns% of/and/from/over/vs %nouns%',
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

    /**
     * @throws EnumInvalidValueException
     */
    private function doBandActivity(Pet $instigatingPet, PetGroup $group)
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
            $group->clearProgress();

            if($group->getSkillRollTotal() < 60)
                $item = 'Single';
            else if($group->getSkillRollTotal() < 100)
                $item = 'EP';
            else //if($group->getSkillRollTotal() < 150)
                $item = 'LP';

            // @TODO:
            /*
            else //if($group->getSkillRollTotal() < 200)
                $item = '???';
            */

            foreach($group->getMembers() as $member)
            {
                $this->inventoryService->receiveItem($item, $member->getOwner(), $member->getOwner(), $member->getName() . '\'s band made this!', LocationEnum::HOME);

                $member->increaseEsteem(mt_rand(8, 12));

                $activityLog = (new PetActivityLog())
                    ->setPet($member)
                    ->setEntry($group->getName() . (mt_rand(1, 5) === 1 ? ' finally' : '') . ' released a new ' . $item . '!')
                    ->setIcon('items/music/note')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                ;

                $this->em->persist($activityLog);

                if($member->getId() === $instigatingPet->getId())
                    $this->responseService->addActivityLog($activityLog);
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
                    ->setEntry($member->getName() . ' met with the rest of the members of ' . $group->getName() . '. ' . self::BAND_ACTIVITY_SENTIMENT_MESSAGES[$sentiment])
                    ->setIcon('items/music/note')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM)
                    ->setChanges($petChanges[$member->getId()]->compare($member))
                ;

                $this->em->persist($activityLog);

                if($member->getId() === $instigatingPet->getId())
                    $this->responseService->addActivityLog($activityLog);
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