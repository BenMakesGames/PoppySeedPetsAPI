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


namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetGroup;
use App\Entity\PetRelationship;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetGroupTypeEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\PetActivity\Group\AstronomyClubService;
use App\Service\PetActivity\Group\BandService;
use App\Service\PetActivity\Group\GamingGroupService;
use App\Service\PetActivity\Group\SportsBallService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class PetGroupService
{
    public const SOCIAL_ENERGY_PER_MEET = 60 * 12;

    public const GROUP_TYPE_NAMES = [
        PetGroupTypeEnum::BAND => 'band',
        PetGroupTypeEnum::ASTRONOMY => 'astronomy lab',
        PetGroupTypeEnum::GAMING => 'gaming group',
        PetGroupTypeEnum::SPORTSBALL => 'sportsball team',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService,
        private readonly BandService $bandService,
        private readonly AstronomyClubService $astronomyClubService,
        private readonly IRandom $squirrel3,
        private readonly GamingGroupService $gamingGroupService,
        private readonly SportsBallService $sportsBallService
    )
    {
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

            case PetGroupTypeEnum::GAMING:
                $this->gamingGroupService->meet($group);
                break;

            case PetGroupTypeEnum::SPORTSBALL:
                $this->sportsBallService->meet($group);
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
            usort($unhappyMembers, fn($a, $b) => $a['happiness'] <=> $b['happiness']);

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

            // if the group has many pets from the same house, we should mark subsequent messages
            // as viewed, so we don't spam the player.
            $alreadyMessagedThisPlayer = in_array($member->getOwner()->getId(), $userIdsMessaged);

            if(!$alreadyMessagedThisPlayer)
                $userIdsMessaged[] = $member->getOwner()->getId();

            $log = $alreadyMessagedThisPlayer
                ? PetActivityLogFactory::createReadLog($this->em, $member, $message)
                : PetActivityLogFactory::createUnreadLog($this->em, $member, $message);

            $log
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout' ]))
            ;
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
        $recruits = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->select('p2')
            ->distinct(true)
            ->leftJoin('App:PetRelationship', 'r', Join::WITH, 'r.pet = p.id')
            ->leftJoin('App:Pet', 'p2', Join::WITH, 'r.relationship = p2.id AND p2.id NOT IN (:groupMembers)')
            ->leftJoin('App:PetRelationship', 'r2', Join::WITH, 'r2.pet = p2.id AND r2.relationship IN (:groupMembers)')
            ->leftJoin('App:PetSkills', 'p2s', Join::WITH, 'p2.skills = p2s.id')
            ->andWhere('r.currentRelationship NOT IN (:unhappyRelationships)')
            ->andWhere('p.id IN (:groupMembers)')
            ->andWhere('r2.currentRelationship NOT IN (:unhappyRelationships)')
            ->orderBy('p2s.music', 'DESC')
            ->setParameter('groupMembers', $group->getMembers()->map(fn(Pet $p) => $p->getId()))
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

            // if the group has many pets from the same house, we should mark subsequent messages
            // as viewed, so we don't spam the player.
            $alreadyMessagedThisPlayer = in_array($member->getOwner()->getId(), $usersAlerted);

            if(!$alreadyMessagedThisPlayer)
                $usersAlerted[] = $member->getOwner()->getId();

            $log = $alreadyMessagedThisPlayer
                ? PetActivityLogFactory::createReadLog($this->em, $member, $message)
                : PetActivityLogFactory::createUnreadLog($this->em, $member, $message);

            $log
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout' ]))
            ;
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

            PetActivityLogFactory::createUnreadLog($this->em, $member, $group->getName() . ' tried to recruit another member, but couldn\'t find anyone. They decided to disband :(')
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RELATIONSHIP_DISCUSSION)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout' ]))
            ;
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

            $alreadyMessagedThisPlayer = in_array($member->getOwner()->getId(), $usersAlerted);

            if(!$alreadyMessagedThisPlayer)
                $usersAlerted[] = $member->getOwner()->getId();

            $log = $alreadyMessagedThisPlayer
                ? PetActivityLogFactory::createReadLog($this->em, $member, $message)
                : PetActivityLogFactory::createUnreadLog($this->em, $member, $message);

            $log
                ->setChanges($changes->compare($member))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout' ]))
            ;
        }
    }

    private static function weightSkill(int $skill): int
    {
        if($skill < 5)
            return 0;
        else if($skill < 10)
            return 1;
        else if($skill < 17)
            return 2;
        else
            return 3;
    }

    private const FriendlyRelationships = [
        RelationshipEnum::FRIEND,
        RelationshipEnum::BFF,
        RelationshipEnum::FWB,
        RelationshipEnum::MATE
    ];

    /**
     * @return Pet[]
     */
    private static function findFriendsWithFewGroups(Pet $pet): array
    {
        $relationshipsWithFewGroups = array_filter(
            $pet->getPetRelationships()->toArray(),
            function(PetRelationship $r) use($pet)
            {
                $otherSide = $r->getRelationship()->getRelationshipWith($pet);

                return
                    //
                    $r->getCurrentRelationship() !== RelationshipEnum::BROKE_UP &&

                    // as long as both pets WANT a friendly relationship, they'll do this
                    $otherSide &&
                    in_array($otherSide->getRelationshipGoal(), self::FriendlyRelationships) &&
                    in_array($r->getRelationshipGoal(), self::FriendlyRelationships) &&

                    // the pets involved must not already have too many group commitments
                    $r->getRelationship()->getGroups()->count() < $r->getRelationship()->getMaximumGroups()
                ;
            }
        );

        return array_map(function(PetRelationship $p) { return $p->getRelationship(); }, $relationshipsWithFewGroups);
    }

    public function createGroup(Pet $pet): ?PetGroup
    {
        /** @var ComputedPetSkills[] $availableFriends */
        $availableFriends = array_values(array_map(function(Pet $pet) {
            return $pet->getComputedSkills();
        }, self::findFriendsWithFewGroups($pet)));

        // the more groups you're in, the more friends you need to start another group
        // (reduces the chances of having duplicate-member groups)
        if(count($availableFriends) < 2 + count($pet->getGroups()) * 2)
            return null;

        $groupTypePreferences = [
            [
                'type' => PetGroupTypeEnum::BAND,
                'description' => self::GROUP_TYPE_NAMES[PetGroupTypeEnum::BAND],
                'icon' => 'groups/band',
                'preference' => 2 + PetGroupService::weightSkill($pet->getSkills()->getMusic()),
            ],
            [
                'type' => PetGroupTypeEnum::ASTRONOMY,
                'description' => self::GROUP_TYPE_NAMES[PetGroupTypeEnum::ASTRONOMY],
                'icon' => 'groups/astronomy',
                'preference' => 2 + PetGroupService::weightSkill($pet->getSkills()->getScience()),
            ],
            [
                'type' => PetGroupTypeEnum::GAMING,
                'description' => self::GROUP_TYPE_NAMES[PetGroupTypeEnum::GAMING],
                'icon' => 'groups/gaming',
                'preference' => 1 + ($pet->getExtroverted() + 1) * 2,
            ],
            [
                'type' => PetGroupTypeEnum::SPORTSBALL,
                'description' => self::GROUP_TYPE_NAMES[PetGroupTypeEnum::SPORTSBALL],
                'icon' => 'groups/gaming',
                'preference' => 2 + PetGroupService::weightSkill($pet->getSkills()->getBrawl()),
            ]
        ];

        $groupType = ArrayFunctions::pick_one_weighted($groupTypePreferences, fn($t) => $t['preference']);
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
                    return $b->getMusic()->getTotal() <=> $a->getMusic()->getTotal();
                });
                break;

            case PetGroupTypeEnum::ASTRONOMY:
                usort($availableFriends, function (ComputedPetSkills $a, ComputedPetSkills $b) {
                    return $b->getScience()->getTotal() <=> $a->getScience()->getTotal();
                });
                break;

            case PetGroupTypeEnum::SPORTSBALL:
                usort($availableFriends, function (ComputedPetSkills $a, ComputedPetSkills $b) {
                    return $b->getBrawl()->getTotal() + $b->getStealth()->getTotal() / 2 <=> $a->getBrawl()->getTotal() + $b->getStealth()->getTotal() / 2;
                });
                break;

            // gaming groups are totally random

            default:
                $this->squirrel3->rngNextShuffle($availableFriends);
        }

        /** @var ComputedPetSkills[] $friendsToInvite */
        $friendsToInvite = array_slice($availableFriends, 0, min(count($availableFriends), $this->squirrel3->rngNextInt(2, $this->squirrel3->rngNextInt(3, 4))));
        $friendNames = array_map(fn(ComputedPetSkills $p) => $p->getPet()->getName(), $friendsToInvite);

        foreach($friendsToInvite as $friend)
        {
            $friendPet = $friend->getPet();
            $friendPet->addGroup($group);

            PetActivityLogFactory::createUnreadLog($this->em, $friendPet, $friendPet->getName() . ' was invited to join ' . $pet->getName() . '\'s new ' . self::GROUP_TYPE_NAMES[$type] . ', ' . $group->getName() . '!')
                ->addInterestingness(PetActivityLogInterestingnessEnum::NEW_RELATIONSHIP)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Group Hangout' ]))
            ;
        }

        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started a new ' . $groupType['description'] . ' with ' . ArrayFunctions::list_nice($friendNames) . '.')
            ->setIcon($groupType['icon']);

        return $group;
    }

    public function generateName(int $type): string
    {
        return match ($type)
        {
            PetGroupTypeEnum::BAND => $this->bandService->generateGroupName(),
            PetGroupTypeEnum::ASTRONOMY => $this->astronomyClubService->generateGroupName(),
            PetGroupTypeEnum::GAMING => $this->gamingGroupService->generateGroupName(),
            PetGroupTypeEnum::SPORTSBALL => $this->sportsBallService->generateGroupName(),
            default => throw new \Exception('Ben forgot to program group names for groups of type "' . $type . '"! (Bad Ben!)'),
        };
    }
}
