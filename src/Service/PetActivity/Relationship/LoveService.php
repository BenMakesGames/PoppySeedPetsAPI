<?php
namespace App\Service\PetActivity\Relationship;

use App\Entity\Pet;
use App\Enum\LoveLanguageEnum;
use App\Enum\MeritEnum;
use App\Enum\RelationshipEnum;
use App\Functions\ArrayFunctions;
use App\Service\PetActivity\PregnancyService;

class LoveService
{
    private $pregnancyService;

    public function __construct(PregnancyService $pregnancyService)
    {
        $this->pregnancyService = $pregnancyService;
    }

    public function expressLove(Pet $giver, Pet $receiver)
    {
        if($giver->hasMerit(MeritEnum::INTROSPECTIVE) || $giver->hasMerit(MeritEnum::EIDETIC_MEMORY) || mt_rand(1, 3) === 1)
            $expression = $receiver->getLoveLanguage();
        else
            $expression = $giver->getLoveLanguage();

        $giver
            ->increaseLove(mt_rand(3, 6))
            ->increaseSafety(mt_rand(3, 6))
            ->increaseEsteem(mt_rand(3, 6))
        ;

        $side = 0;

        if($expression === $giver->getLoveLanguage())
        {
            $giver->increaseLove(mt_rand(2, 4));
        }

        if($expression === $receiver->getLoveLanguage())
        {
            $giver->increaseEsteem(mt_rand(2, 4));

            $receiver
                ->increaseLove(mt_rand(2, 4))
                ->increaseEsteem(mt_rand(2, 4))
            ;

            $side = $expression === $giver->getLoveLanguage() ? 1 : 2;
        }

        $receiver
            ->increaseLove(mt_rand(3, 6))
            ->increaseSafety(mt_rand(3, 6))
            ->increaseEsteem(mt_rand(3, 6))
        ;

        switch($expression)
        {
            case LoveLanguageEnum::TOUCH:
                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    [ 'They had fun!', 'They both had an awesome time!', 'They had fun!' ][$side] . ' ' .
                    $this->sexyTimesEmoji($giver, $receiver) . ' ' .
                    [ $giver->getName() . ' really appreciated the attention!', '', $receiver->getName() . ' really appreciated the attention!' ][$side]
                ;

                if(mt_rand(1, 20) === 1)
                    $this->pregnancyService->getPregnant($giver, $receiver);

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
                    ArrayFunctions::pick_one($services) . ' ' .
                    [ $giver->getName() . ' was happy to feel useful!', $receiver->getName() . ' really appreciated it; ' . $giver->getName() . ' was delighted to help!', $receiver->getName() . ' really appreciated the help!' ][$side]
                ;
                break;

            case LoveLanguageEnum::WORDS:
                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    $giver->getName() . ' ' . (mt_rand(0, $giver->getSkills()->getMusic()) >= 4 ? 'sang a love song for' : 'gave a love poem to') . ' ' . $receiver->getName() . '! ' .
                    [ $receiver->getName() . ' thought it was a little silly, but very cute.', $receiver->getName() . ' loved it! ' . $giver->getName() . ' was delighted!', $receiver->getName() . ' loved it!' ][$side]
                ;
                break;

            case LoveLanguageEnum::TIME:
                $message = $giver->getName() . ' hung out with ' . $receiver->getName() . '. ' .
                    [ $giver->getName() . ' loved just spending the time together!', 'They both had an awesome time!', $receiver->getName() . ' really appreciated the attention!' ][$side]
                ;
                break;

            case LoveLanguageEnum::GIFTS:
                $gift = ArrayFunctions::pick_one([
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
