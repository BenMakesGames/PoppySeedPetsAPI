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
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ParkEvent\KinBallParticipant;
use App\Model\ParkEvent\KinBallTeam;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ParkService;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class KinBallService implements ParkEventInterface
{
    public const ROUNDS_TO_WIN = 2;
    public const TARGET_SCORE = 5;
    public const CRITICAL_SCORE = 3;

    /** @var KinBallTeam[] */
    private array $teams;

    private string $results = '';

    private int $attackingTeam;
    private int $designatedTeam;

    private array $teamWins = [ 0, 0, 0 ];
    private $activeTeams;
    private $teamPoints;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PetRelationshipService $petRelationshipService,
        private readonly PetExperienceService $petExperienceService,
        private readonly TransactionService $transactionService,
        private readonly InventoryService $inventoryService,
        private readonly IRandom $squirrel3,
        private readonly ParkService $parkService,
        private readonly UserStatsService $userStatsRepository
    )
    {
    }

    public function isGoodNumberOfPets(int $petCount): bool
    {
        return $petCount === 12;
    }

    /**
     * @param Pet[] $pets
     */
    public function play($pets): ParkEvent
    {
        if(!$this->isGoodNumberOfPets(count($pets)))
            throw new \InvalidArgumentException('Exactly 12 pets are required to play Kin-Ball.');

        $parkEvent = (new ParkEvent(ParkEventTypeEnum::KIN_BALL))
            ->addParticipants($pets)
        ;

        // set up teams
        $this->teams = [
            new KinBallTeam('black'),
            new KinBallTeam('grey'),
            new KinBallTeam($this->squirrel3->rngNextInt(1, 2) === 1 ? 'blue' : 'pink'),
        ];

        /** @var KinBallParticipant[] $participants */
        $participants = [];

        for($i = 0; $i < 12; $i++)
        {
            $team = $i % 3;

            $participant = new KinBallParticipant($pets[$i], $team);

            $participants[] = $participant;
            $this->teams[$team]->pets[] = $participant;
        }

        $this->attackingTeam = $this->squirrel3->rngNextInt(0, 2);

        foreach($this->teams as $team)
        {
            $this->results .= ucfirst($team->color) . ' Team:' . "\n";
            foreach($team->pets as $participant)
                $this->results .= '* ' . $participant->pet->getName() . "\n";

            $this->results .= "\n";
        }

        $this->results .= 'The first team to win ' . self::ROUNDS_TO_WIN . ' Periods will win the game.' . "\n\n---\n";
        $this->results .= 'The die has been thrown! ' . ucfirst($this->teams[$this->attackingTeam]->color) . ' Team will be the first attacking team of the game!' . "\n\n";

        $period = 0;

        while($this->getGameWinningTeam() === null)
        {
            $period++;
            $this->playPeriod($period);
        }

        $winningTeamIndex = $this->getGameWinningTeam();

        $this->results .= "\n\n---\n\n";
        $this->results .= 'Game Results' . "\n---\n\n";
        $this->results .= '**' . ucfirst($this->teams[$winningTeamIndex]->color) . ' Team wins the game!**' . "\n\n";

        $this->awardExp();

        $parkEvent->setResults($this->results);

        // finally, give pets a chance to meet each other:
        foreach($this->teams as $teamIndex=>$team)
        {
            $teamMembers = array_map(fn(KinBallParticipant $p) => $p->pet, $team->pets);
            $this->petRelationshipService->groupGathering(
                $teamMembers,
                '%p1% and %p2% were on the same team for a Kin-Ball game! :)',
                '%p1% and %p2%, unfortunately, were on the same team during a Kin-Ball game...',
                'Met at a game of Kin-Ball. They were on the same team!',
                '%p1% met %p2% at a game of Kin-Ball. They were on the same team!',
                [ 'Park Event', 'Kin-Ball' ],
                2
            );
        }

        for($i = 0; $i < count($participants) - 1; $i++)
        {
            // don't do duplicate hang-outs!
            for($j = $i + 1; $j < count($participants); $j++)
            {
                $p1 = $participants[$i];
                $p2 = $participants[$j];

                if($p1->team !== $p2->team)
                {
                    $this->petRelationshipService->seeAtGroupGathering(
                        $p1->pet, $p2->pet,
                        '%p1% and %p2% chatted a little after a Kin-Ball game! :)',
                        '%p1% and %p2%, unfortunately, saw each other at a Kin-Ball game...',
                        'Met at a game of Kin-Ball.',
                        '%p1% met %p2% at a game of Kin-Ball.',
                        [ 'Park Event', 'Kin-Ball' ],
                        1
                    );
                }
            }
        }

        $this->parkService->giveOutParticipationRewards($parkEvent, $participants);

        return $parkEvent;
    }

    private function awardExp()
    {
        $affectionTotal = 0;

        foreach($this->teams as $teamIndex=>$team)
        {
            foreach($team->pets as $participant)
                $affectionTotal += $participant->pet->getAffectionLevel();
        }

        $winningTeamIndex = $this->getGameWinningTeam();

        $firstPlaceMoneys = 2 * 12 - $this->squirrel3->rngNextInt(0, 8); // * 12, because there are 12 players
        $firstPlaceMoneys += (int)ceil($affectionTotal / 12); // affection bonus
        $firstPlaceMoneys += (int)floor($firstPlaceMoneys * 3 / 4); // usually there's a second-place prize; not in Kin-Ball!
        $firstPlaceMoneys = (int)ceil($firstPlaceMoneys / 4); // the prize is shared by all four members of the team

        foreach($this->teams as $teamIndex=>$team)
        {
            foreach($team->pets as $participant)
            {
                $expGain = 1;
                $trophyItem = null;

                $state = new PetChanges($participant->pet);

                if($winningTeamIndex === $teamIndex)
                {
                    $participant->isWinner = true;

                    $expGain++;
                    $comment = $participant->pet->getName() . ' earned this in a game of Kin-Ball!';
                    $this->transactionService->getMoney($participant->pet->getOwner(), $firstPlaceMoneys, $comment);
                    $trophyItem = 'Kin-Ball Gold Trophy';
                    $this->userStatsRepository->incrementStat($participant->pet->getOwner(), 'Gold Trophies Earned', 1);
                    $activityLogEntry = $participant->pet->getName() . ' played a game of Kin-Ball, and was on the winning team! They received ' . $firstPlaceMoneys . '~~m~~!';
                }
                else
                    $activityLogEntry = $participant->pet->getName() . ' played a game of Kin-Ball. ' . $participant->pet->getName() . ' wasn\'t on the winning team, but it was still a good game!';

                $participant->pet->increaseSafety(3);
                $participant->pet->increaseLove(3);
                $participant->pet->increaseEsteem(3);

                $log = PetActivityLogFactory::createUnreadLog($this->em, $participant->pet, $activityLogEntry)
                    ->setIcon('icons/activity-logs/park')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::PARK_EVENT)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Park Event', 'Kin-ball' ]))
                ;

                if($trophyItem)
                    $this->inventoryService->petCollectsItem($trophyItem, $participant->pet, $comment, $log);

                if($participant->isWinner)
                    PetBadgeHelpers::awardBadge($this->em, $participant->pet, PetBadgeEnum::FIRST_PLACE_KIN_BALL, $log);

                $this->petExperienceService->gainExp(
                    $participant->pet,
                    $expGain,
                    [ PetSkillEnum::BRAWL ],
                    $log
                );

                $log->setChanges($state->compare($participant->pet));

                $participant->activityLog = $log;
            }
        }

        $this->results .= 'Members of the winning team each receive ' . $firstPlaceMoneys . ' ~~m~~:' . "\n";
        foreach($this->teams[$winningTeamIndex]->pets as $participant)
            $this->results .= '* ' . $participant->pet->getName() . "\n";
    }

    private function assignDesignatedTeam()
    {
        if(count($this->activeTeams) === 2)
        {
            foreach($this->activeTeams as $team)
            {
                if($team !== $this->attackingTeam)
                {
                    $this->designatedTeam = $team;
                    return;
                }
            }
        }

        $defendingScore = $this->getHighestNonAttackingScore();

        $possibleTeams = array_filter(
            $this->getTeamsHavingScore($defendingScore),
            fn($t) => $t !== $this->attackingTeam
        );

        $this->designatedTeam = $this->squirrel3->rngNextFromArray($possibleTeams);
    }

    private function getTeamsHavingScore(int $score)
    {
        $teams = [];

        for($i = 0; $i < count($this->teams); $i++)
        {
            if($this->teamPoints[$i] === $score)
                $teams[] = $i;
        }

        return $teams;
    }

    private function getHighestNonAttackingScore()
    {
        $highest = 0;

        for($i = 0; $i < count($this->teams); $i++)
        {
            if($i === $this->attackingTeam) continue;

            if($this->teamPoints[$i] > $highest)
                $highest = $this->teamPoints[$i];
        }

        return $highest;
    }

    private function getLowestScore()
    {
        $lowest = $this->teamPoints[0];

        for($i = 1; $i < count($this->teams); $i++)
        {
            if($this->teamPoints[$i] < $lowest)
                $lowest = $this->teamPoints[$i];
        }

        return $lowest;
    }

    private function getGameWinningTeam(): ?int
    {
        for($i = 0; $i < count($this->teams); $i++)
        {
            if($this->teamWins[$i] >= self::ROUNDS_TO_WIN)
                return $i;
        }

        return null;
    }

    private function checkForCriticalScores()
    {
        // if we already eliminated a team, then there's nothing to do
        if(count($this->activeTeams) < 3)
            return;

        // if none of the teams have reached a critical score, then there's nothing to do
        if(!ArrayFunctions::any($this->teamPoints, fn(int $score) => $score >= self::CRITICAL_SCORE))
            return;

        $lowestScore = $this->getLowestScore();

        $lowestScoringTeams = [];

        for($i = 0; $i < count($this->teams); $i++)
        {
            if($this->teamPoints[$i] === $lowestScore)
                $lowestScoringTeams[] = $i;
        }

        // if multiple teams are tied for lowest, none are eliminated
        if(count($lowestScoringTeams) > 1)
            return;

        $this->eliminateTeam($lowestScoringTeams[0]);
    }

    private function eliminateTeam(int $team)
    {
        $this->activeTeams = array_filter($this->activeTeams, fn(int $t) => $t !== $team);

        $this->results .= "\n" . '**' . ucfirst($this->teams[$team]->color) . ' Team (' . $this->teamWins[$team] . ' points) has been eliminated this Period!**' . "\n\n";
    }

    private function getPeriodWinningTeam(): ?int
    {
        $winners = [];

        for($i = 0; $i < count($this->teamWins); $i++)
        {
            if($this->teamPoints[$i] >= self::TARGET_SCORE)
                $winners[] = $i;
        }

        if(count($winners) === 1)
            return $winners[0];

        return null;
    }

    private function playPeriod(int $period)
    {
        $this->results .= 'Period ' . $period . "\n---\n\n";

        $this->teamPoints = [ 0, 0, 0 ];
        $this->activeTeams = [ 0, 1, 2 ];

        $round = 0;

        while($this->getPeriodWinningTeam() === null)
        {
            $round++;
            $this->playRound($round);
        }

        $winningTeamIndex = $this->getPeriodWinningTeam();

        $winningTeam = $this->teams[$winningTeamIndex];

        $this->results .= ucfirst($winningTeam->color) . ' Team wins a round!' . "\n\n";

        $this->teamWins[$winningTeamIndex]++;
    }

    private function playRound(int $round)
    {
        $callingPet = $this->getRandomPetFromTeam($this->attackingTeam);
        $this->assignDesignatedTeam();

        $this->results .= $callingPet->pet->getName() . ' (' . ucfirst($this->teams[$this->attackingTeam]->color) . ') designates '  . ucfirst($this->teams[$this->designatedTeam]->color) . ' to defend, and hits the ball!' . "\n";

        $defendingPet = $this->getRandomPetFromTeam($this->designatedTeam);

        $attackRoll = $this->squirrel3->rngNextInt(1, 3 + $callingPet->skill);

        if($attackRoll === 1)
        {
            $this->results .= '* ' . $callingPet->pet->getName() . ' didn\'t hit the ball hard enough!' . "\n";
            $this->givePointToOtherTeams($this->attackingTeam);
        }
        else if($this->squirrel3->rngNextInt(1, 3 + $defendingPet->skill) >= $attackRoll)
        {
            $this->results .= '* ' . $defendingPet->pet->getName() . ' catches the ball.' . "\n";
        }
        else
        {
            $foul = $this->squirrel3->rngNextInt(1, 3);

            if($defendingPet->pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 20) === 1)
            {
                if($foul === 1)
                    $this->results .= '* ' . $defendingPet->pet->getName() . ' tried to catch the ball, and it was totally going to hit below the hips, but ' . $defendingPet->pet->getName() . ' happened to stumble in such a way that made them dip juuuust low enough for it to be a legal touch! Lucky~!' . "\n";
                else if($foul === 2)
                    $this->results .= '* ' . ucfirst($this->teams[$this->designatedTeam]->color) . ' would have failed to catch the ball before it went out of bounds, but ' . $defendingPet->pet->getName() . ' happened to be completely out of position, and right where the ball was headed! Lucky~!' . "\n";
                else // 3
                    $this->results .= '* The ball almost hit the ground before anyone from ' . ucfirst($this->teams[$this->designatedTeam]->color) . ' could catch it, but ' . $defendingPet->pet->getName() . ' tripped while chasing the ball, and happened to catch it just in time! Lucky~!' . "\n";
            }
            else
            {
                if($foul === 1)
                    $this->results .= '* ' . $defendingPet->pet->getName() . ' tried to catch the ball, but it hit below the hips!' . "\n";
                else if($foul === 2)
                    $this->results .= '* ' . ucfirst($this->teams[$this->designatedTeam]->color) . ' failed to catch the ball before it went out of bounds!' . "\n";
                else // 3
                    $this->results .= '* The ball hit the ground before anyone from ' . ucfirst($this->teams[$this->designatedTeam]->color) . ' could catch it!' . "\n";

                $this->givePointToOtherTeams($this->designatedTeam);
            }
        }

        $this->results .= "\n";

        $this->attackingTeam = $this->designatedTeam;
    }

    private function givePointToOtherTeams(int $team)
    {
        foreach($this->activeTeams as $activeTeam)
        {
            if($activeTeam === $team) continue;

            $this->teamPoints[$activeTeam]++;
            $this->results .= '* **' . ucfirst($this->teams[$activeTeam]->color) . ' Team gets a point (' . $this->teamPoints[$activeTeam] . ')**' . "\n";
        }

        $this->checkForCriticalScores();
    }

    private function getRandomPetFromTeam(int $team): KinBallParticipant
    {
        return $this->squirrel3->rngNextFromArray($this->teams[$team]->pets);
    }
}
