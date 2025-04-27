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
use App\Enum\SpiritCompanionStarEnum;
use App\Exceptions\UnreachableException;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ParkEvent\TriDChessParticipant;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ParkService;
use App\Service\PetExperienceService;
use App\Service\PetRelationshipService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class TriDChessService implements ParkEventInterface
{
    public const array ChessPieces = [
        'a pawn', 'a rook', 'a knight', 'a bishop', 'the queen', 'the king',
    ];

    /** @var TriDChessParticipant[] */
    private array $participants;

    /** @var TriDChessParticipant[] */
    private array $winners;

    private array $wins = [];
    private array $defeatedBy = [];

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
        return $petCount === 8 || $petCount === 16 || $petCount === 32;
    }

    /**
     * @param Pet[] $pets
     */
    public function play(array $pets): ParkEvent
    {
        if(!$this->isGoodNumberOfPets(count($pets)))
            throw new \InvalidArgumentException('The number of pets must be 8, 16, or 32.');

        $this->participants = array_map(fn(Pet $pet) => new TriDChessParticipant($pet), $pets);
        $this->winners = array_map(fn(Pet $pet) => new TriDChessParticipant($pet), $pets);

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
            [ 'Park Event', 'Tri-D Chess' ],
            2
        );

        $parkEvent = (new ParkEvent(ParkEventTypeEnum::TRI_D_CHESS))
            ->addParticipants($pets)
            ->setResults($this->results)
        ;

        $this->parkService->giveOutParticipationRewards($parkEvent, $this->participants);

        return $parkEvent;
    }

    private function doRound(): void
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

    private function doMatch(TriDChessParticipant $p1, TriDChessParticipant $p2): int
    {
        $this->results .= $p1->pet->getName() . ' vs ' . $p2->pet->getName() . "\n";

        $move = 2;
        $playOrder = $this->rng->rngNextInt(0, 1);

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
            [ 'Park Event', 'Tri-D Chess' ],
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
            throw new UnreachableException('Neither player lost?? This is a terrible programming error.');
    }

    private function doPlay(TriDChessParticipant $participant, int $healthAdvantage, int $move): int
    {
        $bonus = 0;

        if($this->rng->rngNextInt(1, 400) < $participant->skill && $move > 5)
        {
            $this->results .= '* ' . $participant->pet->getName() . ' made a brilliant play!' . "\n";

            return 10;
        }
        else if($participant->pet->hasMerit(MeritEnum::SPIRIT_COMPANION) && $participant->pet->getSpiritCompanion()->getStar() === SpiritCompanionStarEnum::CASSIOPEIA && $this->rng->rngNextInt(1, 100) <= 3 && $move > 3)
        {
            $this->results .= '* ' . $participant->pet->getName() . '\'s spirit companion nudged ' . $this->rng->rngNextFromArray(self::ChessPieces) . ' forward.';

            switch($this->rng->rngNextInt(1, 3))
            {
                case 1: $this->results .= ' ' . $participant->pet->getName() . ' is confused, but it\'s too late now. Hopefully it works out...' . "\n"; break;
                case 2: $this->results .= ' It\'s a surprisingly-good play!' . "\n"; $bonus = 2; break;
                default: $this->results .= ' Well spotted!' . "\n"; $bonus = 1; break;
            }
        }

        $lowerBounds = min((int)ceil($participant->skill / 2), $participant->skill + $healthAdvantage - 1);

        $damage = $this->rng->rngNextInt($lowerBounds, $participant->skill + $healthAdvantage);

        return max($this->rng->rngNextInt(1, 3), $damage) + $bonus;
    }


    private function awardExp(): void
    {
        $affectionAverage = ArrayFunctions::average($this->participants, fn(TriDChessParticipant $p) => $p->pet->getAffectionLevel());

        $firstPlaceMoneys = 2 * count($this->participants) - $this->rng->rngNextInt(0, 8); // base prize
        $firstPlaceMoneys += (int)ceil($affectionAverage); // affection bonus

        $secondPlaceMoneys = (int)ceil($firstPlaceMoneys * 3 / 4);

        $this->results .= '**' . $this->winners[0]->pet->getName() . ' wins the tournament, and ' . $firstPlaceMoneys . '~~m~~!**' . "<br>\n";

        foreach($this->participants as $participant)
        {
            $expGain = 1;

            $state = new PetChanges($participant->pet);

            $wins = $this->wins[$participant->pet->getId()];
            $trophyItem = null;
            $comment = null;

            if($wins === $this->round)
            {
                $participant->isWinner = true;

                $expGain++;

                $comment = $participant->pet->getName() . ' earned this by getting 1st place in a Tri-D Chess tournament!';
                $this->transactionService->getMoney($participant->pet->getOwner(), $firstPlaceMoneys, $comment);
                $trophyItem = 'Tri-D Chess Gold Trophy';
                $this->userStatsRepository->incrementStat($participant->pet->getOwner(), 'Gold Trophies Earned', 1);

                $activityLogEntry = $participant->pet->getName() . ' played in a Tri-D chess tournament, and won! The whole thing!';
            }
            else if($wins === 0)
                $activityLogEntry = $participant->pet->getName() . ' played in a Tri-D chess tournament, but lost in the first round to ' . $this->defeatedBy[$participant->pet->getId()]->getName() . '. (Next time, ' . $this->defeatedBy[$participant->pet->getId()]->getName() . '!)';
            else
                $activityLogEntry = $participant->pet->getName() . ' played in a Tri-D chess tournament, won ' . $wins . ' ' . ($wins === 1 ? 'round' : 'rounds') . ', and lost to ' . $this->defeatedBy[$participant->pet->getId()]->getName() . ' in round ' . ($wins + 1) . '.';

            if($wins === $this->round - 1)
            {
                $expGain++;

                $comment = $participant->pet->getName() . ' earned this by getting 2nd place in a Tri-D Chess tournament!';
                $this->transactionService->getMoney($participant->pet->getOwner(), $secondPlaceMoneys, $comment);
                $trophyItem = 'Tri-D Chess Silver Trophy';
                $this->userStatsRepository->incrementStat($participant->pet->getOwner(), 'Silver Trophies Earned', 1);

                $this->results .= $participant->pet->getName() . ' got 2nd place, and ' . $secondPlaceMoneys . '~~m~~!';
            }

            $participant->pet->increaseEsteem(2 * $wins);

            $log = PetActivityLogFactory::createUnreadLog($this->em, $participant->pet, $activityLogEntry)
                ->setIcon('icons/activity-logs/park')
                ->addInterestingness(PetActivityLogInterestingnessEnum::PARK_EVENT)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Park Event', 'Tri-D Chess' ]))
            ;

            if($trophyItem && $comment)
                $this->inventoryService->petCollectsItem($trophyItem, $participant->pet, $comment, $log);

            if($participant->isWinner)
                PetBadgeHelpers::awardBadge($this->em, $participant->pet, PetBadgeEnum::FIRST_PLACE_CHESS, $log);

            $this->petExperienceService->gainExp(
                $participant->pet,
                $expGain,
                [ PetSkillEnum::SCIENCE ],
                $log
            );

            $log->setChanges($participant->pet, $state->compare($participant->pet));

            $participant->activityLog = $log;
        }
    }
}
