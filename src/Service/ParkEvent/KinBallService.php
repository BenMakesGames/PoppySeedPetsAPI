<?php
namespace App\Service\ParkEvent;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Functions\ArrayFunctions;
use App\Model\ParkEvent\KinBallParticipant;
use App\Model\ParkEvent\KinBallTeam;

class KinBallService
{
    public const ROUNDS_TO_WIN = 2;
    public const TARGET_SCORE = 5;
    public const CRITICAL_SCORE = 3;

    /** @var KinBallTeam[] */
    private $teams;

    /** @var string */
    private $results = '';

    /** @var integer */
    private $attackingTeam;

    /** @var integer */
    private $designatedTeam;

    /** @var integer */
    private $hitIn;

    private $teamWins = [ 0, 0, 0 ];
    private $activeTeams;
    private $teamPoints;
    private $totalPetSkill;
    private $highestPetSkill = null;
    private $lowestPetSkill = null;

    public function play(ParkEvent $event)
    {
        // set up teams
        $this->teams = [
            new KinBallTeam('black'),
            new KinBallTeam('grey'),
            new KinBallTeam(mt_rand(1, 2) === 1 ? 'blue' : 'pink'),
        ];

        $this->totalPetSkill = 0;

        for($i = 0; $i < 12; $i++)
        {
            $team = $i % 3;

            $participant = new KinBallParticipant($event->getParticipants()[$i], $team);

            $this->teams[$team]->pets[] = $participant;

            $this->totalPetSkill += $participant->skill;

            if($this->highestPetSkill === null || $participant->skill > $this->highestPetSkill)
                $this->highestPetSkill = $participant->skill;

            if($this->lowestPetSkill === null || $participant->skill < $this->lowestPetSkill)
                $this->lowestPetSkill = $participant->skill;
        }

        $this->attackingTeam = mt_rand(0, 2);

        $this->results .= 'The die has been thrown! ' . ucfirst($this->teams[$this->attackingTeam]->color) . ' Team will be the attacking team in round 1!' . "\n\n";

        $period = 0;

        while($this->getGameWinningTeam() === null)
        {
            $period++;
            $this->playPeriod($period);
        }
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

        $this->designatedTeam = $this->getRandomTeamHavingScore($defendingScore);
    }

    private function getRandomTeamHavingScore(integer $score)
    {
        $teams = [];

        for($i = 0; $i < count($this->teams); $i++)
        {
            if($this->teamPoints[$i] === $score)
                $teams[] = $i;
        }

        return ArrayFunctions::pick_one($teams);
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

    private function getGameWinningTeam(): ?integer
    {
        for($i = 0; $i < count($this->teams); $i++)
        {
            if($this->teamWins[$i] === self::ROUNDS_TO_WIN)
                return $i;
        }

        return null;
    }

    private function getPeriodWinningTeam(): ?integer
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

    private function playPeriod(integer $period)
    {
        $this->results .=
            'Period ' . $period . "\n---\n\n"
        ;

        $this->teamPoints = [ 0, 0, 0 ];
        $this->activeTeams = [ 0, 1, 2 ];

        while($this->getPeriodWinningTeam() === null)
        {
            $this->playRound();
        }
    }

    private function playRound()
    {
        $callingPet = $this->getRandomPetFromTeam($this->attackingTeam);
        $this->assignDesignatedTeam();

        $this->results .= $callingPet->getName() . ' designates '  . ucfirst($this->teams[$this->designatedTeam]->color) . ' to defend, and hits the ball!' . "\n\n";

        $controllingPet = $this->getRandomBestPet();
        $foulingPet = $this->getRandomWorstPet();
    }

    private function getRandomPetFromTeam(integer $team): Pet
    {
        return ArrayFunctions::pick_one($this->teams[$team]->pets);
    }

    private function getRandomBestPet(): KinBallParticipant
    {
        $rng = mt_rand(0, $this->totalPetSkill - 1);

        foreach($this->teams as $team)
        {
            foreach($team->pets as $pet)
            {
                if($rng < $pet->skill)
                    return $pet;
                else
                    $rng -= $pet->skill;
            }
        }

        return null;
    }

    private function getRandomWorstPet(): Pet
    {

    }
}