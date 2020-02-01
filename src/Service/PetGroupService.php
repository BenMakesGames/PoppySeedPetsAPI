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
use App\Model\PetChanges;
use App\Repository\PetRepository;
use App\Service\PetActivity\Group\AstronomyClubService;
use App\Service\PetActivity\Group\BandService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class PetGroupService
{
    private $em;
    private $petRepository;
    private $responseService;
    private $petExperienceService;
    private $bandService;
    private $astronomyClubService;

    public function __construct(
        EntityManagerInterface $em, PetRepository $petRepository, ResponseService $responseService,
        PetExperienceService $petExperienceService, BandService $bandService, AstronomyClubService $astronomyClubService
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->bandService = $bandService;
        $this->astronomyClubService = $astronomyClubService;
    }

    public function doGroupActivity(Pet $instigatingPet, PetGroup $group)
    {
        if($this->checkForSplitUp($instigatingPet, $group))
            return;

        if($this->checkForRecruitment($instigatingPet, $group))
            return;

        $this->takesTime($instigatingPet, $group);

        switch ($group->getType())
        {
            case PetGroupTypeEnum::BAND:
                $this->bandService->meet($instigatingPet, $group);
                break;

            case PetGroupTypeEnum::ASTRONOMY:
                $this->astronomyClubService->meet($instigatingPet, $group);
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

    private function checkForSplitUp(Pet $instigatingPet, PetGroup $group): bool
    {
        $unhappyMembers = [];

        foreach($group->getMembers() as $member)
        {
            $happiness = $this->getMemberHappiness($group, $member) + mt_rand(-500, 500) / 100;

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

        $this->takesTime($instigatingPet, $group, PetActivityStatEnum::GROUP_ACTIVITY);

        foreach($group->getMembers() as $member)
        {
            $changes = new PetChanges($member);

            if($member->getId() === $unhappiestPet->getId())
            {
                $member->increaseEsteem(-mt_rand(2, 4));
            }
            else
            {
                $r = $member->getRelationshipWith($unhappiestPet);

                if($r && $r->getHappiness() < 0)
                    $member->increaseSafety(mt_rand(2, 4));
                else
                    $member->increaseLove(-mt_rand(2, 4));
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

            $this->em->persist($logEntry);

            if($instigatingPet->getId() === $member->getId())
                $this->responseService->addActivityLog($logEntry);
        }

        $unhappiestPet->removeGroup($group);

        if(count($group->getMembers()) === 0)
            $this->em->remove($group);

        return true;
    }

    private function checkForRecruitment(Pet $instigatingPet, PetGroup $group): bool
    {
        $numMembers = count($group->getMembers());

        // if the group is too big, DEFINITELY don't recruit
        if($numMembers >= $group->getMaximumSize())
            return false;

        // if the group is not in danger of disbanding, there's a large chance of NOT recruiting
        if($numMembers >= $group->getMinimumSize() && mt_rand(1, $numMembers * 20) > 1)
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
            $this->recruitMember($instigatingPet, $group, $recruits[array_key_first($recruits)]);

            return true;
        }

        // if you failed to recruit, and you don't have enough members, the group might disband
        if(count($group->getMembers()) === 1 || (count($group->getMembers()) < $group->getMinimumSize() && mt_rand(1, 2) === 1))
        {
            $this->disbandGroup($instigatingPet, $group);

            return true;
        }

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

            $this->em->persist($log);

            if($member->getId() === $instigatingPet->getId())
                $this->responseService->addActivityLog($log);
        }

        return true;
    }

    private function disbandGroup(Pet $instigatingPet, PetGroup $group): void
    {
        foreach($group->getMembers() as $member)
        {
            $changes = new PetChanges($member);

            $member
                ->removeGroup($group)
                ->increaseEsteem(-mt_rand(4, 8))
                ->increaseLove(-mt_rand(2, 4))
            ;

            $log = (new PetActivityLog())
                ->setEntry($group->getName() . ' tried to recruit another member, but couldn\'t find anyone. They decided to disband :(')
                ->setPet($member)
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
            ;

            $this->em->persist($log);

            if($member->getId() === $instigatingPet->getId())
                $this->responseService->addActivityLog($log);
        }

        $this->em->remove($group);
    }

    private function recruitMember(Pet $instigatingPet, PetGroup $group, Pet $recruit): void
    {
        $recruit->addGroup($group);

        foreach($group->getMembers() as $member)
        {
            $changes = new PetChanges($member);

            $member
                ->increaseLove(mt_rand(2, 4))
                ->increaseEsteem(mt_rand(2, 4))
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
                        ->increaseEsteem(mt_rand(2, 4))
                        ->increaseSafety(mt_rand(2, 4))
                    ;
                }
            }

            $log = (new PetActivityLog())
                ->setEntry($message)
                ->setPet($member)
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;

            $this->em->persist($log);

            if($member->getId() === $instigatingPet->getId())
                $this->responseService->addActivityLog($log);
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    private function takesTime(Pet $instigatingPet, PetGroup $group)
    {
        foreach($group->getMembers() as $member)
        {
            $time = ($member->getId() === $instigatingPet->getId()) ? mt_rand(45, 60) : 5;

            $this->petExperienceService->spendTime($member, $time, PetActivityStatEnum::GROUP_ACTIVITY, true);
        }
    }

    public function createGroup(Pet $pet): ?PetGroup
    {
        $availableFriends = $this->petRepository->findFriendsWithFewGroups($pet);

        if(count($availableFriends) < 2)
            return null;

        $groupTypePreferences = [
            [
                'type' => PetGroupTypeEnum::BAND,
                'description' => 'band',
                'icon' => 'groups/band',
                'preference' => 5 + $pet->getMusic(),
            ],
            [
                'type' => PetGroupTypeEnum::ASTRONOMY,
                'description' => 'astronomy lab',
                'icon' => 'groups/astronomy',
                'preference' => 5 + $pet->getScience(),
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
                usort($availableFriends, function (Pet $a, Pet $b) {
                    return $b->getMusic() <=> $a->getMusic();
                });
                break;

            case PetGroupTypeEnum::ASTRONOMY:
                usort($availableFriends, function (Pet $a, Pet $b) {
                    return $b->getScience() <=> $a->getScience();
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

        $this->responseService->createActivityLog($pet, $pet->getName() . ' started a new ' . $groupType['description'] . ' with ' . ArrayFunctions::list_nice($friendNames) . '.', $groupType['icon']);

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