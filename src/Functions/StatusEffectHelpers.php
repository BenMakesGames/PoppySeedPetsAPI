<?php
namespace App\Functions;

use App\Entity\Pet;
use App\Entity\StatusEffect;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use Doctrine\ORM\EntityManagerInterface;

final class StatusEffectHelpers
{
    public const BITES = [
        StatusEffectEnum::BITTEN_BY_A_VAMPIRE,
        StatusEffectEnum::BITTEN_BY_A_WERECREATURE
    ];

    public static function isImmuneToStatusEffect(Pet $pet, string $status): bool
    {
        $hasSilverblood = $pet->hasMerit(MeritEnum::SILVERBLOOD);
        $hasVampireBite = $pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE);

        if($status == StatusEffectEnum::WEREFORM && ($hasSilverblood || $hasVampireBite))
            return true;

        $statusIsABite = ArrayFunctions::any(self::BITES, fn($bite) => $bite == $status);

        if($statusIsABite)
        {
            $alreadyHasABite = ArrayFunctions::any(self::BITES, fn($bite) => $pet->hasStatusEffect($bite));
            $immuneToBites = $alreadyHasABite || $hasSilverblood;

            if($immuneToBites)
                return true;
        }

        return false;
    }

    public static function applyStatusEffect(EntityManagerInterface $em, Pet $pet, string $status, int $durationInMinutes)
    {
        if(self::isImmuneToStatusEffect($pet, $status))
            return;

        $maxDuration = StatusEffectHelpers::getStatusEffectMaxDuration($status);

        $statusEffect = $pet->getStatusEffect($status);

        if(!$statusEffect)
        {
            $statusEffect = (new StatusEffect())
                ->setStatus($status);

            $pet->addStatusEffect($statusEffect);

            $em->persist($statusEffect);
        }

        $statusEffect
            ->setTotalDuration(min($maxDuration, $statusEffect->getTotalDuration() + $durationInMinutes))
            ->setTimeRemaining(min($statusEffect->getTotalDuration(), $statusEffect->getTimeRemaining() + $durationInMinutes))
        ;

        $statusEffectsToRemove = [];

        if($status === StatusEffectEnum::WEREFORM)
        {
            $itemsDropped = [];

            if(
                $pet->getTool() &&
                $pet->getTool()->getItem()->getTreasure() &&
                $pet->getTool()->getItem()->getTreasure()->getSilver() > 0
            )
            {
                $itemsDropped[] = $pet->getTool()->getFullItemName();
                EquipmentFunctions::unequipPet($pet);
            }

            if(
                $pet->getHat() &&
                $pet->getHat()->getItem()->getTreasure() &&
                $pet->getHat()->getItem()->getTreasure()->getSilver() > 0
            )
            {
                $itemsDropped[] = $pet->getHat()->getFullItemName();
                EquipmentFunctions::unhatPet($pet);
            }

            if(count($itemsDropped) > 0)
                PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% turned into a Werecreature, and immediately dropped their ' . ArrayFunctions::list_nice($itemsDropped) . '!');
            else
                PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% turned into a Werecreature!');
        }
        else if(mb_substr($status, 0, 8) === 'Focused ')
        {
            $statusEffectsToRemove = array_merge(
                $statusEffectsToRemove,
                array_filter($pet->getStatusEffects()->toArray(), fn(StatusEffect $se) => mb_substr($se->getStatus(), 0, 8) === 'Focused ' && $se->getStatus() !== $status)
            );
        }

        foreach($statusEffectsToRemove as $statusEffect)
            $pet->removeStatusEffect($statusEffect);
    }

    public static function getStatusEffectMaxDuration(string $status)
    {
        switch($status)
        {
            case StatusEffectEnum::CAFFEINATED:
                return 8 * 60;
            case StatusEffectEnum::EGGPLANT_CURSED:
            case StatusEffectEnum::GLITTER_BOMBED:
                return 48 * 60;
            case StatusEffectEnum::HEX_HEXED:
                return 6 * 60;
            default:
                return 24 * 60;
        }
    }
}