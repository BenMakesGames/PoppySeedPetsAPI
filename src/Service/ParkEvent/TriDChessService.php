<?php
namespace App\Service\ParkEvent;

use App\Entity\ParkEvent;
use App\Entity\Pet;
use App\Enum\ParkEventTypeEnum;
use App\Model\ParkEvent\TriDChessParticipant;

class TriDChessService implements ParkEventInterface
{
    /** @var TriDChessParticipant[] */
    private $participants;

    private $results = '';

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

        $round = 0;

        $this->results .= 'Participants in this event:' . "\n";

        foreach($this->participants as $participant)
            $this->results .= '* ' . $participant->pet->getName() . "\n";

        $this->results .= "\n";

        while(count($this->participants) > 1)
        {
            $round++;
            $this->doRound($round);
        }

        $this->results .= '**' . $this->participants[0]->pet->getName() . ' wins the tournament!**';

        return (new ParkEvent())
            ->setType(ParkEventTypeEnum::TRI_D_CHESS)
            ->addParticipants($pets)
            ->setResults($this->results)
        ;
    }

    private function doRound(int $round)
    {
        $this->results .= 'Round ' . $round . "\n---\n\n";

        $winners = [];

        for($i = 0; $i < count($this->participants); $i += 2)
        {
            $pet1 = $this->participants[$i];
            $pet2 = $this->participants[$i + 1];

            $winner = $this->doMatch($pet1, $pet2);

            if($winner === 1)
                $winners[] = $pet1;
            else
                $winners[] = $pet2;
        }

        $this->participants = $winners;
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
        if(mt_rand(1, 400) < $participant->skill && $move > 5)
        {
            $this->results .= '* ' . $participant->pet->getName() . ' made a brilliant play!' . "\n";

            return -10;
        }

        $lowerBounds = min(ceil($participant->skill / 2), $participant->skill + $healthAdvantage - 1);

        $damage = mt_rand($lowerBounds, $participant->skill + $healthAdvantage);

        return max(mt_rand(1, 3), $damage);
    }
}