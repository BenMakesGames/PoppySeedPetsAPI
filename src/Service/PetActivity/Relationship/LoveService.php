<?php
namespace App\Service\PetActivity\Relationship;

use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Entity\PetSkills;
use App\Enum\LoveLanguageEnum;
use App\Enum\MeritEnum;
use App\Enum\RelationshipEnum;
use App\Model\ComputedPetSkills;
use App\Service\PetActivity\PregnancyService;
use App\Service\Squirrel3;

class LoveService
{
    private $pregnancyService;
    private $squirrel3;

    public function __construct(PregnancyService $pregnancyService, Squirrel3 $squirrel3)
    {
        $this->pregnancyService = $pregnancyService;
        $this->squirrel3 = $squirrel3;
    }

    public function expressLove(PetRelationship $givingPet, PetRelationship $receivingPet)
    {
        $giver = $givingPet->getPet();
        $receiver = $receivingPet->getPet();

        if($giver->hasMerit(MeritEnum::INTROSPECTIVE) || $giver->hasMerit(MeritEnum::EIDETIC_MEMORY) || $this->squirrel3->rngNextInt(1, 3) === 1)
            $expression = $receiver->getLoveLanguage();
        else
            $expression = $giver->getLoveLanguage();

        $giver
            ->increaseLove($this->squirrel3->rngNextInt(3, 6))
            ->increaseSafety($this->squirrel3->rngNextInt(3, 6))
            ->increaseEsteem($this->squirrel3->rngNextInt(3, 6))
        ;

        $side = 0;

        if($expression === $giver->getLoveLanguage())
        {
            $giver->increaseLove($this->squirrel3->rngNextInt(2, 4));
        }

        if($expression === $receiver->getLoveLanguage())
        {
            $giver->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $receiver
                ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
            ;

            $side = $expression === $giver->getLoveLanguage() ? 1 : 2;
        }

        $receiver
            ->increaseLove($this->squirrel3->rngNextInt(3, 6))
            ->increaseSafety($this->squirrel3->rngNextInt(3, 6))
            ->increaseEsteem($this->squirrel3->rngNextInt(3, 6))
        ;

        // the message array triplets refer to whether the expression is the giver's love language, both of the pets' love languages,
        // or the receiver's love language. ex:
        //   [ $giver->getName() . ' liked the thing.', 'They both liked the thing.', $receiver->getName() . ' really liked the thing.' ]
        switch($expression)
        {
            case LoveLanguageEnum::TOUCH:
                $sexyTimesChance = $this->sexyTimeChances($giver, $receiver, $givingPet->getCurrentRelationship());

                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ';

                if($this->squirrel3->rngNextInt(1, 100) <= $sexyTimesChance)
                {
                    $message .= $this->sexyTimesEmoji($giver, $receiver) . ' ' .
                        [ 'They had fun!', 'They both had an awesome time!', 'They had fun!' ][$side] . ' ' .
                        $this->sexyTimesEmoji($giver, $receiver) . ' ' .
                        [ $giver->getName() . ' really enjoyed giving ' . $receiver->getName() . ' the attention!', '', $receiver->getName() . ' really appreciated the attention!' ][$side]
                    ;

                    if($this->squirrel3->rngNextInt(1, 20) === 1)
                        $this->pregnancyService->getPregnant($giver, $receiver);
                }
                else
                {
                    if($this->squirrel3->rngNextBool())
                    {
                        $message .= ':) ' .
                            [
                                $giver->getName() . ' surprised ' . $receiver->getName() . ' with a kiss!',
                                $giver->getName() . ' surprised ' . $receiver->getName() . ' with a kiss! ' . $receiver->getName() . ' surprised ' . $giver->getName() . ' with a kiss of their own!',
                                $receiver->getName() . ' was delighted to receive a surprise kiss!'
                            ][$side]
                        ;
                    }
                    else
                    {
                        $message .= ':) ' .
                            [ $giver->getName() . ' really enjoyed cuddling with ' . $receiver->getName() . '!', 'They both enjoyed cuddling for a while!', $receiver->getName() . ' really appreciated the cuddles!' ][$side]
                        ;
                    }
                }

                break;

            case LoveLanguageEnum::ACTS:
                $services = [
                    $giver->getName() . ' helped ' . $receiver->getName() . ' tidy up their room.',
                ];

                if($receiver->getSpecies()->getSheds()->getName() === 'Feathers')
                    $services[] = $giver->getName() . ' helped ' . $receiver->getName() . ' clean their feathers.';
                else if($receiver->getSpecies()->getSheds()->getName() === 'Scales')
                    $services[] = $giver->getName() . ' helped ' . $receiver->getName() . ' clean their scales.';

                if($receiver->getOwner()->getUnlockedBasement())
                    $services[] = $giver->getName() . ' helped ' . $receiver->getName() . ' tidy up the basement a little.';

                if($receiver->getTool() !== null)
                    $services[] = $giver->getName() . ' cleaned & patched up ' . $receiver->getName() . '\'s ' . $receiver->getTool()->getItem()->getName() . '.';

                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $this->squirrel3->rngNextFromArray($services) . ' ' .
                    [ $giver->getName() . ' was happy to feel useful!', $receiver->getName() . ' really appreciated it; ' . $giver->getName() . ' was delighted to help!', $receiver->getName() . ' really appreciated the help!' ][$side]
                ;
                break;

            case LoveLanguageEnum::WORDS:
                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $giver->getName() . ' ' . ($this->squirrel3->rngNextInt(0, $giver->getSkills()->getMusic()) >= 4 ? 'sang a love song for' : 'gave a love poem to') . ' ' . $receiver->getName() . '! ' .
                    [ $receiver->getName() . ' thought it was a little silly, but very cute.', $receiver->getName() . ' loved it! ' . $giver->getName() . ' was delighted!', $receiver->getName() . ' loved it!' ][$side]
                ;

                break;

            case LoveLanguageEnum::TIME:
                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $this->squirrel3->rngNextFromArray([
                        'They took a walk on the beach, and got into a staring contest with some seagulls.',
                        'They went to the park, and watched some pets ' . $this->squirrel3->rngNextFromArray([ 'jousting', 'play Tri-D chess', 'play Kin-Ball' ]) . '.',
                        'They went to the plaza and made wishes at the fountain.',
                        'They hung out at the bookstore cafe for a while.'
                    ]) . ' ' .
                    [ $giver->getName() . ' loved just spending the time together!', 'They both enjoyed the time spent together!', $receiver->getName() . ' really appreciated the time they spent together!' ][$side]
                ;
                break;

            case LoveLanguageEnum::GIFTS:
                $gift = $this->squirrel3->rngNextFromArray([
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

    private function getAncestorIds(Pet $pet)
    {
        // we'll go back two generations; and include yourself:
        $ancestorIds = [
            $pet->getId(),
        ];

        if($pet->getMom())
        {
            $ancestorIds[] = $pet->getMom()->getId();
            $ancestorIds[] = $pet->getDad()->getId();
        }

        if($pet->getMom() && $pet->getMom()->getMom())
        {
            $ancestorIds[] = $pet->getMom()->getMom()->getId();
            $ancestorIds[] = $pet->getMom()->getDad()->getId();
        }

        if($pet->getDad() && $pet->getDad()->getMom())
        {
            $ancestorIds[] = $pet->getDad()->getMom()->getId();
            $ancestorIds[] = $pet->getDad()->getDad()->getId();
        }

        return $ancestorIds;
    }

    public function sexyTimeChances(Pet $p1, Pet $p2, string $relationshipType): int
    {
        // parent-child are implemented as BFFs, which have a tiny chance of sexy times that I don't think we need in this game :P
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

    public function sexyTimesEmoji(Pet $p1, Pet $p2)
    {
        if($p1->hasMerit(MeritEnum::PREHENSILE_TONGUE) || $p2->hasMerit(MeritEnum::PREHENSILE_TONGUE))
            return ';P';
        else
            return ';)';
    }

}
