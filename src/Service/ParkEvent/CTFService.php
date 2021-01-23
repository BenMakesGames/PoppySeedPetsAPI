<?php
namespace App\Service\ParkEvent;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\ParkEventTypeEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ParkEvent\CTFParticipant;
use App\Model\ParkEvent\CTFTeam;
use App\Model\ParkEvent\KinBallParticipant;
use App\Model\ParkEvent\KinBallTeam;
use App\Model\PetChanges;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\PetService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class CTFService implements ParkEventInterface
{
    public const ROUNDS_TO_WIN = 2;
    public const TARGET_SCORE = 5;
    public const CRITICAL_SCORE = 3;

    /** @var CTFTeam[] */
    private $teams;

    /** @var string */
    private $results = '';

    private $em;
    private $petRelationshipService;
    private $petExperienceService;
    private $transactionService;
    private $squirrel3;

    public function __construct(
        EntityManagerInterface $em, PetRelationshipService $petRelationshipService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, Squirrel3 $squirrel3
    )
    {
        $this->em = $em;
        $this->petRelationshipService = $petRelationshipService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->squirrel3 = $squirrel3;
    }

    public function isGoodNumberOfPets(int $petCount): bool
    {
        return in_array($petCount, [ 10, 12, 14 ]);
    }

    /**
     * @param Pet[] $pets
     */
    public function play($pets): ParkEvent
    {
        if(!$this->isGoodNumberOfPets(count($pets)))
            throw new \InvalidArgumentException('10, 12, or 14 pets are required to play Capture the Flag.');

        $parkEvent = (new ParkEvent())
            ->addParticipants($pets)
            ->setType(ParkEventTypeEnum::CTF)
        ;

        $colors = $this->squirrel3->rngNextSubsetFromArray([
            'red', 'blue', 'green', 'gold', 'purple', 'white', 'black'
        ], 2);

        // set up teams
        $this->teams = [
            new CTFTeam($colors[0]),
            new CTFTeam($colors[1]),
        ];

        $this->totalPetSkill = 0;
        $this->totalPetAntiSkill = 0;

        /** @var CTFParticipant[] $participants */
        $participants = [];

        for($i = 0; $i < count($pets); $i++)
        {
            $team = $i % 2;

            $participants[] = $this->teams[$team]->AddParticipant($pets[$i], $this->squirrel3);
        }

        $participantIds = [];

        while($this->getGameWinningTeam() === null)
        {
            if(count($participantIds) <= 2)
            {
                $participantIds = range(0, count($participants) - 1);
                $this->squirrel3->rngNextShuffle($participantIds);
            }

            $id = array_shift($participantIds);

            $this->takeAction($participants[$id]);
        }

        $winningTeam = $this->getGameWinningTeam();

        $this->results .= '**' . ucfirst($winningTeam->color) . ' Team wins the game!**' . "\n\n---\n";

        $this->awardExp($participants);

        $parkEvent->setResults($this->results);

        // finally, give pets a chance to meet each other:
        foreach($this->teams as $team)
        {
            $teamMembers = array_map(function(CTFParticipant $p) { return $p->pet; }, $team->members);
            $this->petRelationshipService->groupGathering(
                $teamMembers,
                '%p1% and %p2% were on the same team for a game of Capture the Flag! :)',
                '%p1% and %p2%, unfortunately, were on the same team during a game of Capture the Flag...',
                'Met at a game of Capture the Flag. They were on the same team!',
                '%p1% met %p2% at a game of Capture the Flag. They were on the same team!',
                2
            );
        }

        for($i = 0; $i < count($participants) - 1; $i++)
        {
            // don't do duplicate hangouts!
            for($j = $i + 1; $j < count($participants); $j++)
            {
                $p1 = $participants[$i];
                $p2 = $participants[$j];

                if($p1->team !== $p2->team)
                {
                    $this->petRelationshipService->seeAtGroupGathering(
                        $p1->pet, $p2->pet,
                        '%p1% and %p2% chatted a little after a game of Capture the Flag! :)',
                        '%p1% and %p2%, unfortunately, saw each other during a game of Capture the Flag...',
                        'Met at a game of Capture the Flag.',
                        '%p1% met %p2% at a game of Capture the Flag.',
                        1
                    );
                }
            }
        }

        return $parkEvent;
    }

    /**
     * @param CTFParticipant[] $participants
     */
    private function awardExp(array $participants)
    {
        $skillTotal = 0;

        foreach($participants as $participant)
        {
            $skillTotal += $participant->skill;
        }

        $skillAverage = ceil($skillTotal / count($participants));

        $winningTeam = $this->getGameWinningTeam();

        foreach($this->teams as $team)
        {
            foreach($team->members as $participant)
            {
                $expGain = ceil($participant->skill / 12);

                $state = new PetChanges($participant->pet);

                if($winningTeam === $team)
                {
                    $expGain++;
                    $this->transactionService->getMoney($participant->pet->getOwner(), $skillAverage, $participant->pet->getName() . ' earned this in a game of Capture the Flag!');
                    $activityLogEntry = $participant->pet->getName() . ' played a game of Capture the Flag, and was on the winning team! They received ' . $skillAverage . '~~m~~!';
                }
                else
                    $activityLogEntry = $participant->pet->getName() . ' played a game of Capture the Flag. ' . $participant->pet->getName() . ' wasn\'t on the winning team, but it was still a good game!';

                $this->petExperienceService->gainExp(
                    $participant->pet,
                    $expGain,
                    [ PetSkillEnum::STEALTH ]
                );

                $participant->pet->increaseSafety(3);
                $participant->pet->increaseLove(3);
                $participant->pet->increaseEsteem(3);

                $log = (new PetActivityLog())
                    ->setPet($participant->pet)
                    ->setEntry($activityLogEntry)
                    ->setChanges($state->compare($participant->pet))
                    ->setIcon('icons/menu/park')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::PARK_EVENT)
                ;

                $this->em->persist($log);
            }
        }

        $this->results .= 'Members of the winning team each receive ' . $skillAverage . ' ~~m~~:' . "\n";
        foreach($winningTeam->members as $participant)
            $this->results .= '* ' . $participant->pet->getName() . "\n";
    }

    private function getGameWinningTeam(): ?CTFTeam
    {
        for($i = 0; $i < count($this->teams); $i++)
        {
            if($this->teams[$i]->points == 2)
                return $this->teams[$i];
        }

        return null;
    }

    private function takeAction(CTFParticipant $p)
    {

    }
}
