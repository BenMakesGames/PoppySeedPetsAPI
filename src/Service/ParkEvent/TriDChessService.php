<?php
namespace App\Service\ParkEvent;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\ParkEventTypeEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Functions\ArrayFunctions;
use App\Model\ParkEvent\TriDChessParticipant;
use App\Model\PetChanges;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use Doctrine\ORM\EntityManagerInterface;

class TriDChessService implements ParkEventInterface
{
    public const CHESS_PIECES = [
        'a pawn', 'a rook', 'a knight', 'a bishop', 'the queen', 'the king',
    ];

    /** @var TriDChessParticipant[] */
    private $participants;

    /** @var TriDChessParticipant[] */
    private $winners;

    private $wins = [];
    private $defeatedBy = [];

    private $results = '';

    private $round = 0;

    private $petExperienceService;
    private $em;
    private $petRelationshipService;

    public function __construct(
        PetExperienceService $petExperienceService, EntityManagerInterface $em, PetRelationshipService $petRelationshipService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->petRelationshipService = $petRelationshipService;
    }

    public function isGoodNumberOfPets(int $petCount): bool
    {
        return $petCount === 8 || $petCount === 16 || $petCount === 32;
    }

    /**
     * @param Pet[] $pets
     */
    public function play($pets): ParkEvent
    {
        if(!$this->isGoodNumberOfPets(count($pets)))
            throw new \InvalidArgumentException('The number of pets must be 8, 16, or 32.');

        $this->participants = array_map(function(Pet $pet) { return new TriDChessParticipant($pet); }, $pets);
        $this->winners = array_map(function(Pet $pet) { return new TriDChessParticipant($pet); }, $pets);

        foreach($this->participants as $p)
            $this->wins[$p->pet->getId()] = 0;

        $this->round = 0;

        while(count($this->winners) > 1)
        {
            $this->round++;
            $this->doRound();
        }

        $this->awardExp();

        $this->petRelationshipService->groupGathering(
            $pets,
            '%p1% and %p2% chatted a little after a Tri-D Chess tournament.',
            '%p1% and %p2%, unfortunately, saw each other at a Tri-D Chess tournament...',
            'Met at a Tri-D Chess tournament.',
            '%p1% met %p2% at a Tri-D Chess tournament.',
            2
        );

        return (new ParkEvent())
            ->setType(ParkEventTypeEnum::TRI_D_CHESS)
            ->addParticipants($pets)
            ->setResults($this->results)
        ;
    }

    private function doRound()
    {
        $this->results .= 'Round ' . $this->round . "\n---\n\n";

        $winners = [];

        for($i = 0; $i < count($this->winners); $i += 2)
        {
            $pet1 = $this->winners[$i];
            $pet2 = $this->winners[$i + 1];

            $winner = $this->doMatch($pet1, $pet2);

            if($winner === 1)
            {
                $winners[] = $pet1;
                $this->wins[$pet1->pet->getId()]++;
                $this->defeatedBy[$pet2->pet->getId()] = $pet1->pet;
            }
            else
            {
                $winners[] = $pet2;
                $this->wins[$pet2->pet->getId()]++;
                $this->defeatedBy[$pet1->pet->getId()] = $pet2->pet;
            }
        }

        $this->winners = $winners;
    }

    private function doMatch(TriDChessParticipant $p1, TriDChessParticipant $p2)
    {
        $this->results .= $p1->pet->getName() . ' vs ' . $p2->pet->getName() . "\n";

        $move = 0;
        $playOrder = mt_rand(0, 1);

        // the internet claims that the average number of moves is 40.
        // this simulation expects pets to have a 50/50 chance to make progress, in an evenly-matched game.
        $p1Health = 20;
        $p2Health = 20;

        while($p1Health > 0 && $p2Health > 0)
        {
            $move++;

            if(($move + $playOrder) % 2 === 0)
            {
                $p1Attack = $this->doPlay($p1, $p1Health - $p2Health, $move);
                $p2Health -= $p1Attack;
            }
            else
            {
                $p2Attack = $this->doPlay($p2, $p2Health - $p1Health, $move);
                $p1Health -= $p2Attack;
            }
        }

        $this->petRelationshipService->seeAtGroupGathering(
            $p1->pet,
            $p2->pet,
            '%p1% and %p2% chatted a little during their Tri-D Chess tournament match.',
            '%p1% and %p2% ended up having to play a game of Tri-D Chess together...',
            'Met during a match at a Tri-D Chess tournament.',
            '%p1% and %p2% met during a match at a Tri-D Chess tournament.',
            3
        );

        if($p1Health <= -5)
        {
            $this->results .= '* ' . $p1->pet->getName() . ' forfeits on turn ' . $move . ".\n\n";
            return 2;
        }
        else if($p2Health <= -5)
        {
            $this->results .= '* ' . $p2->pet->getName() . ' forfeits on turn ' . $move . ".\n\n";
            return 1;
        }
        else if($p1Health <= 0)
        {
            $this->results .= '* ' . $p2->pet->getName() . ' declares checkmate on turn ' . $move . ".\n\n";
            return 2;
        }
        else if($p2Health <= 0)
        {
            $this->results .= '* ' . $p1->pet->getName() . ' declares checkmate on turn ' . $move . ".\n\n";
            return 1;
        }
        else
            throw new \Exception('Neither player lost?? This is a terrible programming error.');
    }

    private function doPlay(TriDChessParticipant $participant, int $healthAdvantage, int $move)
    {
        $bonus = 0;

        if(mt_rand(1, 400) < $participant->skill && $move > 5)
        {
            $this->results .= '* ' . $participant->pet->getName() . ' made a brilliant play!' . "\n";

            return 10;
        }
        else if($participant->pet->hasMerit(MeritEnum::SPIRIT_COMPANION) && $participant->pet->getSpiritCompanion()->getStar() === SpiritCompanionStarEnum::CASSIOPEIA && mt_rand(1, 100) <= 3 && $move > 3)
        {
            $this->results .= '* ' . $participant->pet->getName() . '\'s spirit companion nudged ' . ArrayFunctions::pick_one(self::CHESS_PIECES) . ' forward.';

            switch(mt_rand(1, 3))
            {
                case 1: $this->results .= ' ' . $participant->pet->getName() . ' is confused, but it\'s too late now. Hopefully it works out...' . "\n"; break;
                case 2: $this->results .= ' It\'s a surprisingly-good play!' . "\n"; $bonus = 2; break;
                default: $this->results .= ' Well spotted!' . "\n"; $bonus = 1; break;
            }
        }

        $lowerBounds = min(ceil($participant->skill / 2), $participant->skill + $healthAdvantage - 1);

        $damage = mt_rand($lowerBounds, $participant->skill + $healthAdvantage);

        return max(mt_rand(1, 3), $damage) + $bonus;
    }


    private function awardExp()
    {
        $skillTotal = 0;

        foreach($this->participants as $participant)
            $skillTotal += $participant->skill;

        $firstPlaceMoneys = ceil($skillTotal * $this->round / 10);
        $secondPlaceMoneys = ceil($firstPlaceMoneys * 3 / 4);

        $this->results .= '**' . $this->winners[0]->pet->getName() . ' wins the tournament, and ' . $firstPlaceMoneys . '~~m~~!**' . "<br>\n";

        foreach($this->participants as $participant)
        {
            $expGain = ceil($participant->skill / 12);

            $state = new PetChanges($participant->pet);

            $wins = $this->wins[$participant->pet->getId()];

            if($wins === $this->round)
            {
                $expGain++;
                $participant->pet->getOwner()->increaseMoneys($firstPlaceMoneys);
                $activityLogEntry = $participant->pet->getName() . ' played in a Tri-D chess tournament, and won! The whole thing!';
            }
            else if($wins === 0)
                $activityLogEntry = $participant->pet->getName() . ' played in a Tri-D chess tournament, but lost in the first round to ' . $this->defeatedBy[$participant->pet->getId()]->getName() . '. (Next time, ' . $this->defeatedBy[$participant->pet->getId()]->getName() . '!)';
            else
                $activityLogEntry = $participant->pet->getName() . ' played in a Tri-D chess tournament, won ' . $wins . ' ' . ($wins === 1 ? 'round' : 'rounds') . ', and lost to ' . $this->defeatedBy[$participant->pet->getId()]->getName() . ' in round ' . ($wins + 1) . '.';

            if($wins === $this->round - 1)
            {
                $expGain++;
                $participant->pet->getOwner()->increaseMoneys($secondPlaceMoneys);
                $this->results .= $participant->pet->getName() . ' got 2nd place, and ' . $secondPlaceMoneys . '~~m~~!';
            }

            $this->petExperienceService->gainExp(
                $participant->pet,
                $expGain,
                [ PetSkillEnum::COMPUTER ]
            );

            $participant->pet->increaseEsteem(2 * $wins);

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

}