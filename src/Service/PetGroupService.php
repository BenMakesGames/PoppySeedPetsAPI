<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetGroup;
use App\Enum\EnumInvalidValueException;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetGroupTypeEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetRepository;
use App\Service\PetActivity\Group\AstronomyClubService;
use App\Service\PetActivity\Group\BandService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class PetGroupService
{
    public const SOCIAL_ENERGY_PER_MEET = 60 * 12;

    public const GROUP_TYPE_NAMES = [
        PetGroupTypeEnum::BAND => 'band',
        PetGroupTypeEnum::ASTRONOMY => 'astronomy lab'
    ];

    private $em;
    private $petRepository;
    private $responseService;
    private $petExperienceService;
    private $bandService;
    private $astronomyClubService;
    private $squirrel3;

    public function __construct(
        EntityManagerInterface $em, PetRepository $petRepository, ResponseService $responseService,
        PetExperienceService $petExperienceService, BandService $bandService, AstronomyClubService $astronomyClubService,
        Squirrel3 $squirrel3
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->bandService = $bandService;
        $this->astronomyClubService = $astronomyClubService;
        $this->squirrel3 = $squirrel3;
    }

    public function doGroupActivity(PetGroup $group)
    {
        $group->spendSocialEnergy(PetGroupService::SOCIAL_ENERGY_PER_MEET);

        if($this->checkForSplitUp($group))
            return;

        if($this->checkForRecruitment($group))
            return;

        switch ($group->getType())
        {
            case PetGroupTypeEnum::BAND:
                $this->bandService->meet($group);
                break;

            case PetGroupTypeEnum::ASTRONOMY:
                $this->astronomyClubService->meet($group);
                break;

            default:
                throw new \Exception('Unhandled group type "' . $group->getType() . '"');
        }
    }

    public function getMemberHappiness(PetGroup $group, Pet $pet)
    {
        // array_reduce is NOT easier to read (and doesn't seem more CPU-efficient, especially since we have to convert toArray())
        $happiness = $pet->getEsteem();

        foreach($group->getMembers() as $member)
        {
            if($member->getId() === $pet->getId()) continue;

            $relationship = $pet->getRelationshipWith($member);

            if($relationship === null) continue;

            $happiness += $relationship->getHappiness();
        }

        return $happiness;
    }

    private function checkForSplitUp(PetGroup $group): bool
    {
        $unhappyMembers = [];

        foreach($group->getMembers() as $member)
        {
            $happiness = $this->getMemberHappiness($group, $member) + $this->squirrel3->rngNextInt(-500, 500) / 100;

            if($happiness < 0)
            {
                $unhappyMembers[] = [
                    'pet' => $member,
                    'happiness' => $happiness
                ];
            }
        }

        if(count($unhappyMembers) === 0)
            return false;

        // sort by happiness, ascending
        if(count($unhappyMembers) > 1)
            usort($unhappyMembers, function($a, $b) { return $a['happiness'] <=> $b['happiness']; });

        /** @var Pet $unhappiestPet */
        $unhappiestPet = $unhappyMembers[0]['pet'];

        $userIdsMessaged = [];

        foreach($group->getMembers() as $member)
        {
            $changes = new PetChanges($member);

            if($member->getId() === $unhappiestPet->getId())
            {
                $member->increaseEsteem(-$this->squirrel3->rngNextInt(2, 4));
            }
            else
            {
                $r = $member->getRelationshipWith($unhappiestPet);

                if($r && $r->getHappiness() < 0)
                    $member->increaseSafety($this->squirrel3->rngNextInt(2, 4));
                else
                    $member->increaseLove(-$this->squirrel3->rngNextInt(2, 4));
            }

            $message = count($group->getMembers()) === 1
                ? ($unhappiestPet->getName() . ' abandoned ' . $group->getName() . '...')
                : ($unhappiestPet->getName() . ' left ' . $group->getName() . '...')
            ;

            $logEntry = (new PetActivityLog())
                ->setPet($member)
                ->setEntry($message)
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
            ;

            // if the group has many pets from the same house, we should mark subsequent messages
            // as viewed, so we don't spam the player.
            if(in_array($member->getOwner()->getId(), $userIdsMessaged))
                $logEntry->setViewed();
            else
                $userIdsMessaged[] = $member->getOwner()->getId();

            $this->em->persist($logEntry);
        }

        $unhappiestPet->removeGroup($group);

        if(count($group->getMembers()) === 0)
            $this->em->remove($group);

        return true;
    }

    private function checkForRecruitment(PetGroup $group): bool
    {
        $numMembers = count($group->getMembers());

        // if the group is too big, DEFINITELY don't recruit
        if($numMembers >= $group->getMaximumSize())
            return false;

        // if the group is not in danger of disbanding, there's a large chance of NOT recruiting
        if($numMembers >= $group->getMinimumSize() && $this->squirrel3->rngNextInt(1, $numMembers * 20) > 1)
            return false;

        /** @var Pet[] $recruit */
        $recruits = $this->petRepository->createQueryBuilder('p')
            ->select('p2')
            ->distinct(true)
            ->innerJoin('App:PetRelationship', 'r', Join::WITH, 'r.pet = p.id')
            ->leftJoin('App:Pet', 'p2', Join::WITH, 'r.relationship = p2.id AND p2.id NOT IN (:groupMembers)')
            ->leftJoin('App:PetRelationship', 'r2', Join::WITH, 'r2.pet = p2.id AND r2.relationship IN (:groupMembers)')
            ->leftJoin('App:PetSkills', 'p2s', Join::WITH, 'p2.skills = p2s.id')
            ->andWhere('r.currentRelationship NOT IN (:unhappyRelationships)')
            ->andWhere('p.id IN (:groupMembers)')
            ->andWhere('r2.currentRelationship NOT IN (:unhappyRelationships)')
            ->orderBy('p2s.music', 'DESC')
            ->setParameter('groupMembers', $group->getMembers()->map(function(Pet $p) { return $p->getId(); }))
            ->setParameter('unhappyRelationships', [ RelationshipEnum::BROKE_UP, RelationshipEnum::DISLIKE ])
            ->getQuery()
            ->execute()
        ;

        $recruits = array_filter($recruits, function(Pet $p) {
            return count($p->getGroups()) < $p->getMaximumGroups();
        });

        if(count($recruits) > 0)
        {
            $this->recruitMember($group, $recruits[array_key_first($recruits)]);

            return true;
        }

        // if you failed to recruit, and you don't have enough members, the group might disband
        if(count($group->getMembers()) === 1 || (count($group->getMembers()) < $group->getMinimumSize() && $this->squirrel3->rngNextInt(1, 2) === 1))
        {
            $this->disbandGroup($group);

            return true;
        }

        $usersAlerted = [];

        foreach($group->getMembers() as $member)
        {
            $message = $group->getName() . ' tried to recruit another member, but couldn\'t find anyone.';

            if(count($group->getMembers()) < $group->getMinimumSize())
                $message .= ' They decided to try again, later...';

            $log = (new PetActivityLog())
                ->setEntry($message)
                ->setPet($member)
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;

            if(!in_array($member->getOwner()->getId(), $usersAlerted))
                $usersAlerted[] = $member->getOwner()->getId();
            else
                $log->setViewed();

            $this->em->persist($log);
        }

        return true;
    }

    private function disbandGroup(PetGroup $group): void
    {
        foreach($group->getMembers() as $member)
        {
            $changes = new PetChanges($member);

            $member
                ->removeGroup($group)
                ->increaseEsteem(-$this->squirrel3->rngNextInt(4, 8))
                ->increaseLove(-$this->squirrel3->rngNextInt(2, 4))
            ;

            $log = (new PetActivityLog())
                ->setEntry($group->getName() . ' tried to recruit another member, but couldn\'t find anyone. They decided to disband :(')
                ->setPet($member)
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
            ;

            $this->em->persist($log);
        }

        $this->em->remove($group);
    }

    private function recruitMember(PetGroup $group, Pet $recruit): void
    {
        $usersAlerted = [];

        $recruit->addGroup($group);

        foreach($group->getMembers() as $member)
        {
            $changes = new PetChanges($member);

            $member
                ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
            ;

            if($member->getId() === $recruit->getId())
            {
                $message = $member->getName() . ' was invited to join ' . $group->getName() . '! They accepted!';
            }
            else
            {
                $message = $group->getName() . ' invited ' . $recruit->getName() . ' to join; they accepted!';

                // if the group was at risk of disbanding, a special message, and the pets feel extra good about it
                if(count($group->getMembers()) === $group->getMinimumSize())
                {
                    $message .= ' ' . $group->getName() . ' is saved!';

                    $member
                        ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                    ;
                }
            }

            $log = (new PetActivityLog())
                ->setEntry($message)
                ->setPet($member)
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;

            if(!in_array($member->getOwner()->getId(), $usersAlerted))
                $usersAlerted[] = $member->getOwner()->getId();
            else
                $log->setViewed();

            $this->em->persist($log);
        }
    }

    public function createGroup(Pet $pet): ?PetGroup
    {
        $petWithSkills = $pet->getComputedSkills();

        /** @var ComputedPetSkills[] $availableFriends */
        $availableFriends = array_map(function(Pet $pet) {
            return $pet->getComputedSkills();
        }, $this->petRepository->findFriendsWithFewGroups($pet));

        // the more groups you're in, the more friends you need to start another group
        // (reduces the chances of having duplicate-member groups)
        if(count($availableFriends) < 2 + count($pet->getGroups()) * 2)
            return null;

        $groupTypePreferences = [
            [
                'type' => PetGroupTypeEnum::BAND,
                'description' => self::GROUP_TYPE_NAMES[PetGroupTypeEnum::BAND],
                'icon' => 'groups/band',
                'preference' => 5 + $petWithSkills->getMusic()->getTotal(),
            ],
            [
                'type' => PetGroupTypeEnum::ASTRONOMY,
                'description' => self::GROUP_TYPE_NAMES[PetGroupTypeEnum::ASTRONOMY],
                'icon' => 'groups/astronomy',
                'preference' => 5 + $petWithSkills->getScience()->getTotal(),
            ]
        ];

        $groupType = ArrayFunctions::pick_one_weighted($groupTypePreferences, function($t) { return $t['preference']; });
        $type = $groupType['type'];

        $group = (new PetGroup())
            ->setType($type)
            ->setName($this->generateName($type))
        ;

        $this->em->persist($group);

        $pet->addGroup($group);

        switch($type)
        {
            case PetGroupTypeEnum::BAND:
                usort($availableFriends, function (ComputedPetSkills $a, ComputedPetSkills $b) {
                    return $b->getMusic() <=> $a->getMusic();
                });
                break;

            case PetGroupTypeEnum::ASTRONOMY:
                usort($availableFriends, function (ComputedPetSkills $a, ComputedPetSkills $b) {
                    return $b->getScience() <=> $a->getScience();
                });
                break;

            default:
                $this->squirrel3->rngNextShuffle($availableFriends);
        }

        /** @var ComputedPetSkills[] $friendsToInvite */
        $friendsToInvite = array_slice($availableFriends, 0, min(count($availableFriends), $this->squirrel3->rngNextInt(2, $this->squirrel3->rngNextInt(3, 4))));
        $friendNames = array_map(function(ComputedPetSkills $p) { return $p->getPet()->getName(); }, $friendsToInvite);

        foreach($friendsToInvite as $friend)
        {
            $friendPet = $friend->getPet();
            $friendPet->addGroup($group);

            $log = (new PetActivityLog())
                ->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP)
                ->setPet($friendPet)
                ->setEntry($friendPet->getName() . ' was invited to join ' . $pet->getName() . '\'s new ' . self::GROUP_TYPE_NAMES[$type] . ', ' . $group->getName() . '!')
            ;

            $this->em->persist($log);
        }

        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started a new ' . $groupType['description'] . ' with ' . ArrayFunctions::list_nice($friendNames) . '.', $groupType['icon']);

        return $group;
    }

    private function generateName(int $type): string
    {
        switch($type)
        {
            case PetGroupTypeEnum::BAND:
                return $this->bandService->generateBandName();
            case PetGroupTypeEnum::ASTRONOMY:
                return $this->astronomyClubService->generateGroupName();
            default:
                throw new \Exception('Ben forgot to program group names for groups of type "' . $type . '"! (Bad Ben!)');
        }
    }
}
