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


namespace App\Service\ParkEvent;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\ParkEventTypeEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ParkEvent\JoustingClashResult;
use App\Model\ParkEvent\JoustingParticipant;
use App\Model\ParkEvent\JoustingTeam;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ParkService;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class JoustingService implements ParkEventInterface
{
    /** @var JoustingTeam[] */
    private array $participants;

    /** @var JoustingTeam[] */
    private array $winners;

    /** @var JoustingParticipant[] */
    private array $individualParticipants;

    private string $results = '';

    private int $round = 0;

    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly EntityManagerInterface $em,
        private readonly PetRelationshipService $petRelationshipService,
        private readonly TransactionService $transactionService,
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng,
        private readonly ParkService $parkService,
        private readonly UserStatsService $userStatsRepository
    )
    {
    }

    public function isGoodNumberOfPets(int $petCount): bool
    {
        return $petCount === 16 || $petCount === 32;
    }

    public function getPetSkill(Pet $pet): float
    {
        return $pet->getSkills()->getStrength() * 3 + $pet->getSkills()->getStamina() * 2 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl() / 2;
    }

    /**
     * @param Pet[] $pets
     */
    public function play(array $pets): ParkEvent
    {
        if(!$this->isGoodNumberOfPets(count($pets)))
            throw new \InvalidArgumentException('The number of pets must be 16, or 32.');

        $this->participants = [];

        usort($pets, fn(Pet $a, Pet $b) => $this->getPetSkill($a) <=> $this->getPetSkill($b));

        $numberOfPets = count($pets);

        $this->individualParticipants = [];

        for($x = 0; $x < $numberOfPets / 2; $x++)
        {
            $pet1 = $pets[$x];
            $pet2 = $pets[$numberOfPets - $x - 1];

            $this->individualParticipants[$pet1->getId()] = new JoustingParticipant($pet1);
            $this->individualParticipants[$pet2->getId()] = new JoustingParticipant($pet2);

            $this->participants[] = new JoustingTeam($pet1, $pet2);

            $this->petRelationshipService->groupGathering(
                [ $pet1, $pet2 ],
                '%p1% and %p2% are jousting partners in a Jousting tournament! They chatted a little while warming up.',
                '%p1% and %p2%, unfortunately, must work together as a team during a Jousting tournament!',
                'Met as jousting partners in a Jousting tournament.',
                '%p1% and %p2% met at a Jousting tournament. They were jousting partners!',
                [ 'Park Event', 'Jousting' ],
                100
            );
        }

        $this->winners = $this->participants;

        $this->round = 0;

        while(count($this->winners) > 1)
        {
            $this->round++;
            $this->doRound();
        }

        $this->results .= 'Tournament Results' . "\n---\n\n";

        $this->awardExp();

        $parkEvent = (new ParkEvent(ParkEventTypeEnum::Jousting))
            ->addParticipants($pets)
            ->setResults($this->results)
        ;

        $this->parkService->giveOutParticipationRewards($parkEvent, $this->individualParticipants);

        return $parkEvent;
    }

    private function doRound(): void
    {
        $this->results .= 'Round ' . $this->round . "\n---\n\n";

        $winners = [];

        for($i = 0; $i < count($this->winners); $i += 2)
        {
            $team1 = $this->winners[$i];
            $team2 = $this->winners[$i + 1];

            $winner = $this->doMatch($team1, $team2);

            if($winner === 1)
            {
                $team1->wins++;
                $team2->defeatedBy = $team1;
                $winners[] = $team1;
            }
            else
            {
                $team2->wins++;
                $team1->defeatedBy = $team2;
                $winners[] = $team2;
            }
        }

        $this->winners = $winners;
    }

    private function doMatch(JoustingTeam $team1, JoustingTeam $team2): int
    {
        $team1->randomizeRoles($this->rng);
        $team2->randomizeRoles($this->rng);

        $this->results .= '#### ' . $team1->getTeamName() . ' vs ' . $team2->getTeamName() . "\n";

        $team1Relationship = $team1->rider->getRelationshipWith($team1->mount);
        $team2Relationship = $team2->rider->getRelationshipWith($team2->mount);

        $team1CurrentRelationship = $team1Relationship === null ? null : $team1Relationship->getCurrentRelationship();
        $team2CurrentRelationship = $team2Relationship === null ? null : $team2Relationship->getCurrentRelationship();

        $team1WontWorkTogether = $team1CurrentRelationship === RelationshipEnum::Dislike || $team1CurrentRelationship === RelationshipEnum::BrokeUp;
        $team2WontWorkTogether = $team2CurrentRelationship === RelationshipEnum::Dislike || $team2CurrentRelationship === RelationshipEnum::BrokeUp;

        // @TODO a chance that they "power through", and work together, anyway?

        if($team1WontWorkTogether && $team2WontWorkTogether)
        {
            $winningTeam = $this->rng->rngNextInt(1, 2);

            $this->results .= $team1->rider->getName() . ' and ' . $team1->mount->getName() . ' refuse to work with one another! But the same is true for ' . $team2->rider->getName() . ' and ' . $team2->mount->getName() . '! There\'s nothing to do but flip a coin; ' . ($winningTeam === 1 ? $team1->getTeamName() : $team2->getTeamName()) . ' "wins"...' . "\n\n";

            return $winningTeam;
        }
        else if($team1WontWorkTogether)
        {
            $this->results .= $team1->rider->getName() . ' and ' . $team1->mount->getName() . ' refuse to work with one another! ' . $team2->getTeamName() . ' wins by default!' . "\n\n";
            return 2;
        }
        else if($team2WontWorkTogether)
        {
            $this->results .= $team2->rider->getName() . ' and ' . $team2->mount->getName() . ' refuse to work with one another! ' . $team1->getTeamName() . ' wins by default!' . "\n\n";
            return 1;
        }

        $rivalries = [];

        $riderRiderRelationship = $team1->rider->getRelationshipWith($team2->rider);
        $riderMountRelationship = $team1->rider->getRelationshipWith($team2->mount);
        $mountRiderRelationship = $team1->mount->getRelationshipWith($team2->rider);
        $mountMountRelationship = $team1->mount->getRelationshipWith($team2->mount);

        if($riderRiderRelationship && $riderRiderRelationship->getCurrentRelationship() === RelationshipEnum::FriendlyRival)
            $rivalries[] = [ $team1->rider, $team2->rider ];

        if($riderMountRelationship && $riderMountRelationship->getCurrentRelationship() === RelationshipEnum::FriendlyRival)
            $rivalries[] = [ $team1->rider, $team2->mount ];

        if($mountRiderRelationship && $mountRiderRelationship->getCurrentRelationship() === RelationshipEnum::FriendlyRival)
            $rivalries[] = [ $team1->mount, $team2->rider ];

        if($mountMountRelationship && $mountMountRelationship->getCurrentRelationship() === RelationshipEnum::FriendlyRival)
            $rivalries[] = [ $team1->mount, $team2->mount ];

        if(count($rivalries) > 0)
        {
            $this->rng->rngNextShuffle($rivalries);

            foreach($rivalries as $rivalry)
            {
                $i = $this->rng->rngNextInt(0, 1);

                $reaction = $this->rng->rngNextFromArray([
                    'narrows their eyes at',
                    'sticks out their ' . ($rivalry[$i]->hasMerit(MeritEnum::PREHENSILE_TONGUE) ? 'prehensile ' : '') . 'tongue at',
                    'makes a face at',
                    'taunts',
                    'belittles',
                    'spits ' . ($rivalry[$i]->hasMerit(MeritEnum::BURPS_MOTHS) ? 'a moth ' : '') . 'at',
                ]);

                $this->results .= $rivalry[$i]->getName() . ' ' . $reaction . ' their rival, ' . $rivalry[1 - $i]->getName() . "!\n";
            }

            $this->results .= "\n";
        }

        $team1Points = 0;
        $team2Points = 0;

        for($round = 0; $round < 4; $round++)
        {
            $clashResult = new JoustingClashResult($this->rng, $team1, $team2);

            $this->results .= '1. ' . $this->describeClash($clashResult) . "\n";

            if($clashResult->rider1BrokeLance) $team1Points++;
            if($clashResult->rider1DismountedRider2) $team1Points++;
            if($clashResult->rider1StumbledMount2) $team1Points++;

            if($clashResult->rider2BrokeLance) $team2Points++;
            if($clashResult->rider2DismountedRider1) $team2Points++;
            if($clashResult->rider2StumbledMount1) $team2Points++;

            // we want every combination of rider/mount on both teams to face one another:

            // A/B vs 1/2
            // B/A vs 1/2
            // A/B vs 2/1
            // B/A vs 2/1

            // ^ that is accomplished by this:

            $team1->switchRoles();

            if($round == 1)
                $team2->switchRoles();
        }

        $this->results .= "\n";

        if($team1Points === $team2Points)
        {
            $this->results .= 'The jousters tie, each with ' . $team1Points . ' points!';
            if($team1Points === 0 && $this->rng->rngNextInt(1, 10) === 1)
                $this->results .= ' Let the judgement be that they jousted poorly!';

            $this->results .= ' The draw shall be resolved with a race; everyone is the horse!' . "\n\n";

            $tieBreakers = [ 0.1, 0.2, 0.3, 0.4 ];
            $this->rng->rngNextShuffle($tieBreakers);

            $results = [
                [ 'name' => $team1->rider->getName(), 'team' => 1, 'roll' => $this->rng->rngNextInt(1, 10 + $team1->rider->getSkills()->getStrength()) + $tieBreakers[0] ],
                [ 'name' => $team1->mount->getName(), 'team' => 1, 'roll' => $this->rng->rngNextInt(1, 10 + $team1->mount->getSkills()->getStrength()) + $tieBreakers[1] ],
                [ 'name' => $team2->rider->getName(), 'team' => 2, 'roll' => $this->rng->rngNextInt(1, 10 + $team2->rider->getSkills()->getStrength()) + $tieBreakers[2] ],
                [ 'name' => $team2->mount->getName(), 'team' => 2, 'roll' => $this->rng->rngNextInt(1, 10 + $team2->mount->getSkills()->getStrength()) + $tieBreakers[3] ],
            ];

            usort($results, fn($a, $b) => $b['roll'] <=> $a['roll']);

            $winningTeam = $results[0]['team'];
            $winningPet = $results[0]['name'];

            if($winningTeam === 1)
            {
                $this->results .= $winningPet . ' wins the race! ' . $team1->getTeamName() . ' wins the joust!' . "\n\n";
            }
            else
            {
                $this->results .= $winningPet . ' wins the race! ' . $team2->getTeamName() . ' wins the joust!' . "\n\n";
            }

            return $winningTeam;
        }
        else if($team1Points > $team2Points)
        {
            $this->results .= $team1->getTeamName() . ' wins ' . $team1Points . ' to ' . $team2Points . '!' . "\n\n";
            return 1;
        }
        else // $team1Points < $team2Points
        {
            $this->results .= $team2->getTeamName() . ' wins ' . $team2Points . ' to ' . $team1Points . '!' . "\n\n";
            return 2;
        }
    }

    private function describeClash(JoustingClashResult $result): string
    {
        $team1 = $result->team1;
        $team2 = $result->team2;

        if(!$result->rider1Hit && !$result->rider2Hit)
            return $team1->rider->getName() . ' and ' . $team2->rider->getName() . ' pass each other; neither was able to hit the other!';

        if($result->rider1Hit && $result->rider2Hit)
            $describeLanceStrikes = 'their lances each striking the other';
        else if($result->rider1Hit)
            $describeLanceStrikes = $team1->rider->getName() . ' landing the hit';
        else //if($result->rider2Hit)
            $describeLanceStrikes = $team2->rider->getName() . ' landing the hit';

        $description = '';

        if($result->boringClash)
        {
            $description .= $team1->rider->getName() . ' and ' . $team2->rider->getName() . ' clash, ' . $describeLanceStrikes . ', but no lance was broken, and no one fell or stumbled!';
        }
        else
        {
            $description .= $team1->rider->getName() . ' and ' . $team2->rider->getName() . ' clash, ' . $describeLanceStrikes . '! ';

            if($result->rider1BrokeLance && $result->rider2BrokeLance)
                $description .= 'The two lances shatter simultaneously! ';
            else if($result->rider1BrokeLance)
                $description .= 'But only ' . $team1->rider->getName() . '\'s lance shattered! ';
            else if($result->rider2BrokeLance)
                $description .= 'But only ' . $team2->rider->getName() . '\'s lance shattered! ';
            else
                $description .= 'Neither lance was broken! ';

            $falls = [];

            if($result->rider1DismountedRider2 && $result->rider2DismountedRider1)
                $falls[] = 'both riders were thrown from their mounts';
            else if($result->rider1DismountedRider2)
                $falls[] = $team2->rider->getName() . ' was thrown from their mount';
            else if($result->rider2DismountedRider1)
                $falls[] = $team1->rider->getName() . ' was thrown from their mount';

            if($result->rider1StumbledMount2 && $result->rider2StumbledMount1)
                $falls[] = 'both mounts stumbled from the impact';
            else if($result->rider1StumbledMount2)
                $falls[] = $team2->mount->getName() . ' stumbled from the impact';
            else if($result->rider2StumbledMount1)
                $falls[] = $team1->mount->getName() . ' stumbled from the impact';

            if(count($falls) > 0)
                $description .= ucfirst(ArrayFunctions::list_nice($falls)) . '!';
        }

        return $description;
    }

    private function awardExp(): void
    {
        $affectionTotal = 0;

        foreach($this->participants as $participant)
            $affectionTotal += $participant->rider->getAffectionLevel() + $participant->mount->getAffectionLevel();

        $affectionAverage = $affectionTotal / (count($this->participants) << 1);

        $firstPlaceMoneys = 2 * count($this->participants) - $this->rng->rngNextInt(0, 8); // base prize
        $firstPlaceMoneys += (int)ceil($affectionAverage); // affection bonus
        $firstPlaceMoneys = (int)ceil($firstPlaceMoneys / 2); // divide by two, because two pets share the prize

        $secondPlaceMoneys = (int)ceil($firstPlaceMoneys * 3 / 4);

        $this->results .= '**' . $this->winners[0]->getTeamName() . ' wins the tournament, and ' . $firstPlaceMoneys . '~~m~~!**' . "<br>\n";

        foreach($this->participants as $team)
        {
            $this->rewardPet($team, $team->rider, $team->mount, $firstPlaceMoneys, $secondPlaceMoneys);
            $this->rewardPet($team, $team->mount, $team->rider, $firstPlaceMoneys, $secondPlaceMoneys);

            if($team->wins === $this->round - 1)
                $this->results .= $team->getTeamName() . ' got 2nd place, and ' . $secondPlaceMoneys . '~~m~~!';
        }
    }

    private function rewardPet(JoustingTeam $team, Pet $pet, Pet $teamMate, int $firstPlaceMoneys, int $secondPlaceMoneys): void
    {
        $changes = new PetChanges($pet);

        $exp = 1;
        $trophy = null;

        if($team->wins === $this->round)
        {
            $exp++;

            $trophy = [
                'item' => 'Jousting Gold Trophy',
                'comment' => $pet->getName() . ' earned this by getting 1st place in a Jousting tournament with ' . $teamMate->getName() . '!'
            ];

            $this->transactionService->getMoney($pet->getOwner(), $firstPlaceMoneys, $trophy['comment']);
            $this->userStatsRepository->incrementStat($pet->getOwner(), 'Gold Trophies Earned', 1);

            $log = $pet->getName() . ' played in a Jousting tournament with ' . $teamMate->getName() . ', and won! The whole thing!';
        }
        else if($team->wins === 0)
        {
            $log = $pet->getName() . ' played in a Jousting tournament with ' . $teamMate->getName() . ', but lost in the first round to ' . $team->defeatedBy->getTeamName() . '.';
        }
        else
        {
            $log = $pet->getName() . ' played in a Jousting tournament with ' . $teamMate->getName() . ', won ' . $team->wins . ' ' . ($team->wins === 1 ? 'round' : 'rounds') . ', and lost to ' . $team->defeatedBy->getTeamName() . ' in round ' . ($team->wins + 1) . '.';
        }

        if($team->wins === $this->round - 1)
        {
            $exp++;

            $trophy = [
                'item' => 'Jousting Silver Trophy',
                'comment' => $pet->getName() . ' earned this by getting 2nd place in a Jousting tournament with ' . $teamMate->getName() . '!'
            ];

            $this->transactionService->getMoney($pet->getOwner(), $secondPlaceMoneys, $trophy['comment']);
            $this->userStatsRepository->incrementStat($pet->getOwner(), 'Silver Trophies Earned', 1);
        }

        $pet->increaseEsteem(2 * $team->wins);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, $log)
            ->setIcon('icons/activity-logs/park')
            ->addInterestingness(PetActivityLogInterestingness::ParkEvent)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Park Event', 'Jousting' ]))
        ;

        if($trophy)
            $this->inventoryService->petCollectsItem($trophy['item'], $pet, $trophy['comment'], $log);

        if($team->wins === $this->round)
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::FirstPlaceJousting, $log);

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::Brawl ], $log);

        $log->setChanges($changes->compare($pet));

        $this->individualParticipants[$pet->getId()]->activityLog = $log;
        $this->individualParticipants[$pet->getId()]->isWinner = $team->wins === $this->round;
    }
}
