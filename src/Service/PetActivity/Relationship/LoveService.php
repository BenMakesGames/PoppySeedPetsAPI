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


namespace App\Service\PetActivity\Relationship;

use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Enum\LoveLanguageEnum;
use App\Enum\MeritEnum;
use App\Enum\RelationshipEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Service\IRandom;
use App\Service\PetActivity\PregnancyService;

class LoveService
{
    public function __construct(
        private readonly PregnancyService $pregnancyService,
        private readonly IRandom $rng
    )
    {
    }

    public function expressLove(PetRelationship $givingPet, PetRelationship $receivingPet): string
    {
        $giver = $givingPet->getPet();
        $receiver = $receivingPet->getPet();

        if($giver->hasMerit(MeritEnum::INTROSPECTIVE) || $giver->hasMerit(MeritEnum::EIDETIC_MEMORY) || $this->rng->rngNextInt(1, 3) === 1)
            $expression = $receiver->getLoveLanguage();
        else
            $expression = $giver->getLoveLanguage();

        $giver
            ->increaseLove($this->rng->rngNextInt(3, 5))
            ->increaseSafety($this->rng->rngNextInt(3, 5))
            ->increaseEsteem($this->rng->rngNextInt(3, 5))
        ;

        $side = 0;

        if($expression === $giver->getLoveLanguage())
        {
            $giver->increaseLove($this->rng->rngNextInt(2, 4));
        }

        if($expression === $receiver->getLoveLanguage())
        {
            $giver->increaseEsteem($this->rng->rngNextInt(2, 4));

            $receiver
                ->increaseLove($this->rng->rngNextInt(2, 4))
                ->increaseEsteem($this->rng->rngNextInt(2, 4))
            ;

            $side = $expression === $giver->getLoveLanguage() ? 1 : 2;
        }

        $receiver
            ->increaseLove($this->rng->rngNextInt(3, 5))
            ->increaseSafety($this->rng->rngNextInt(3, 5))
            ->increaseEsteem($this->rng->rngNextInt(3, 5))
        ;

        // the message array triplets refer to whether the expression is the giver's love language, both of the pets' love languages,
        // or the receiver's love language. ex:
        //   [ $giver->getName() . ' liked the thing.', 'They both liked the thing.', $receiver->getName() . ' really liked the thing.' ]
        switch($expression)
        {
            case LoveLanguageEnum::Touch:
                $sexyTimesChance = $this->sexyTimeChances($giver, $receiver, $givingPet->getCurrentRelationship());

                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ';

                if($this->rng->rngNextInt(1, 100) <= $sexyTimesChance)
                {
                    $cordial = $giver->hasStatusEffect(StatusEffectEnum::CORDIAL) || $receiver->hasStatusEffect(StatusEffectEnum::CORDIAL);

                    if($cordial)
                    {
                        $fun = 'They had a simply _wonderful_ time!';
                        $giver->increaseLove(4)->increaseSafety(4)->increaseEsteem(4);
                        $receiver->increaseLove(4)->increaseSafety(4)->increaseEsteem(4);
                    }
                    else
                        $fun = [ 'They had fun!', 'They both had an awesome time!', 'They had fun!' ][$side];

                    $message .=
                        $fun . ' ' .
                        $this->sexyTimesEmoji($giver, $receiver) . ' ' .
                        [ $giver->getName() . ' really enjoyed giving ' . $receiver->getName() . ' the attention!', '', $receiver->getName() . ' really appreciated the attention!' ][$side]
                    ;

                    if($this->rng->rngNextInt(1, 20) === 1)
                        $this->pregnancyService->getPregnant($giver, $receiver);
                }
                else
                {
                    if($this->rng->rngNextBool())
                    {
                        $message .= [
                            $giver->getName() . ' surprised ' . $receiver->getName() . ' with a kiss!',
                            $giver->getName() . ' surprised ' . $receiver->getName() . ' with a kiss! ' . $receiver->getName() . ' surprised ' . $giver->getName() . ' with a kiss of their own!',
                            $receiver->getName() . ' was delighted to receive a surprise kiss!'
                        ][$side];
                    }
                    else
                    {
                        $message .= [
                            $giver->getName() . ' really enjoyed cuddling with ' . $receiver->getName() . '!',
                            'They both enjoyed cuddling for a while!',
                            $receiver->getName() . ' really appreciated the cuddles!'
                        ][$side];
                    }
                }

                break;

            case LoveLanguageEnum::Acts:
                $services = [
                    $giver->getName() . ' helped ' . $receiver->getName() . ' tidy up their room.',
                ];

                if($receiver->getSpecies()->getSheds()->getName() === 'Feathers')
                    $services[] = $giver->getName() . ' helped ' . $receiver->getName() . ' clean their feathers.';
                else if($receiver->getSpecies()->getSheds()->getName() === 'Scales')
                    $services[] = $giver->getName() . ' helped ' . $receiver->getName() . ' clean their scales.';

                if($receiver->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
                    $services[] = $giver->getName() . ' helped ' . $receiver->getName() . ' tidy up the basement a little.';

                if($receiver->getTool() !== null)
                    $services[] = $giver->getName() . ' cleaned & patched up ' . $receiver->getName() . '\'s ' . $receiver->getTool()->getItem()->getName() . '.';

                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $this->rng->rngNextFromArray($services) . ' ' .
                    [ $giver->getName() . ' was happy to feel useful!', $receiver->getName() . ' really appreciated it; ' . $giver->getName() . ' was delighted to help!', $receiver->getName() . ' really appreciated the help!' ][$side]
                ;
                break;

            case LoveLanguageEnum::Words:
                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $giver->getName() . ' ' . ($this->rng->rngNextInt(0, $giver->getSkills()->getMusic()) >= 4 ? 'sang a love song for' : 'gave a love poem to') . ' ' . $receiver->getName() . '! ' .
                    [ $receiver->getName() . ' thought it was a little silly, but very cute.', $receiver->getName() . ' loved it! ' . $giver->getName() . ' was delighted!', $receiver->getName() . ' loved it!' ][$side]
                ;

                break;

            case LoveLanguageEnum::Time:
                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $this->rng->rngNextFromArray([
                        'They took a walk on the beach, and got into a staring contest with some seagulls.',
                        'They went to the park, and watched some pets ' . $this->rng->rngNextFromArray([ 'jousting', 'play Tri-D chess', 'play Kin-Ball' ]) . '.',
                        'They went to the plaza and made wishes at the fountain.',
                        'They hung out at the bookstore cafe for a while.'
                    ]) . ' ' .
                    [ $giver->getName() . ' loved just spending the time together!', 'They both enjoyed the time spent together!', $receiver->getName() . ' really appreciated the time they spent together!' ][$side]
                ;
                break;

            case LoveLanguageEnum::Gifts:
                $gift = $this->rng->rngNextFromArray([
                    'a small gift',
                    'a gift',
                    'a small present',
                    'some food to eat together'
                ]);

                if($gift === 'some food to eat together')
                {
                    $giver->increaseFood(2);
                    $receiver->increaseFood(2);
                }

                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $giver->getName() . ' brought ' . $receiver->getName() . ' ' . $gift . '! ' .
                    [ $receiver->getName() . ' thought it was a little silly, but very cute.', $receiver->getName() . ' loved it! ' . $giver->getName() . ' was delighted!', $receiver->getName() . ' loved it!' ][$side]
                ;
                break;

            default:
                throw new \InvalidArgumentException('Unknown love language "' . $expression . '"');
        }

        return $message;
    }

    public function isTooCloselyRelatedForSex(Pet $p1, Pet $p2): bool
    {
        $p1Ancestors = $this->getAncestorIds($p1);
        $p2Ancestors = $this->getAncestorIds($p2);

        return count(array_intersect($p1Ancestors, $p2Ancestors)) > 0;
    }

    /**
     * @return int[]
     */
    private function getAncestorIds(Pet $pet): array
    {
        // we'll go back two generations; and include yourself:
        $ancestorIds = [
            $pet->getId(),
        ];

        if($pet->getMom())
            $ancestorIds[] = $pet->getMom()->getId();

        if($pet->getDad())
            $ancestorIds[] = $pet->getDad()->getId();

        if($pet->getMom() && $pet->getMom()->getMom())
            $ancestorIds[] = $pet->getMom()->getMom()->getId();

        if($pet->getMom() && $pet->getMom()->getDad())
            $ancestorIds[] = $pet->getMom()->getDad()->getId();

        if($pet->getDad() && $pet->getDad()->getMom())
            $ancestorIds[] = $pet->getDad()->getMom()->getId();

        if($pet->getDad() && $pet->getDad()->getDad())
            $ancestorIds[] = $pet->getDad()->getDad()->getId();

        return $ancestorIds;
    }

    public function sexyTimeChances(Pet $p1, Pet $p2, string $relationshipType): int
    {
        // parent-child are implemented as BFFs, which have a tiny chance of sexy times that
        // I don't think we need in this game >_>
        if($this->isTooCloselyRelatedForSex($p1, $p2))
            return 0;

        $totalDrive = $p1->getComputedSkills()->getSexDrive()->getTotal() + $p2->getComputedSkills()->getSexDrive()->getTotal();

        switch($relationshipType)
        {
            case RelationshipEnum::BFF:
                return max(0, $totalDrive + 1);

            case RelationshipEnum::FWB:
                if($totalDrive <= -3)
                    return 5;
                if($totalDrive === -2)
                    return 10;
                else if($totalDrive === -1)
                    return 20;
                else if($totalDrive === 0)
                    return 30;
                else if($totalDrive === 1)
                    return 55;
                else if($totalDrive === 2)
                    return 80;
                else //if($totalDrive >= 3)
                    return 95;

            case RelationshipEnum::MATE:
                if($totalDrive <= -3)
                    return 1;
                if($totalDrive === -2)
                    return 5;
                else if($totalDrive === -1)
                    return 10;
                else if($totalDrive === 0)
                    return 20;
                else if($totalDrive === 1)
                    return 40;
                else if($totalDrive === 2)
                    return 60;
                else //if($totalDrive >= 3)
                    return 80;

            default:
                return 0;
        }
    }

    public function sexyTimesEmoji(Pet $p1, Pet $p2): string
    {
        if($p1->hasMerit(MeritEnum::PREHENSILE_TONGUE) || $p2->hasMerit(MeritEnum::PREHENSILE_TONGUE))
            return ';P';
        else
            return ';)';
    }

}
