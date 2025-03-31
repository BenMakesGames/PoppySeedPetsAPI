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
        else if(str_starts_with($status, 'Focused ('))
        {
            // the "Focused" family of status effects are mutually exclusive
            $statusEffectsToRemove = array_merge(
                $statusEffectsToRemove,
                array_filter($pet->getStatusEffects()->toArray(), fn(StatusEffect $se) => str_starts_with($se->getStatus(), 'Focused (') && $se->getStatus() !== $status)
            );
        }
        else if(str_starts_with($status, 'Fated ('))
        {
            // the "Fated" family of status effects are mutually exclusive
            $statusEffectsToRemove = array_merge(
                $statusEffectsToRemove,
                array_filter($pet->getStatusEffects()->toArray(), fn(StatusEffect $se) => str_starts_with($se->getStatus(), 'Fated (') && $se->getStatus() !== $status)
            );
        }

        foreach($statusEffectsToRemove as $statusEffect)
            $pet->removeStatusEffect($statusEffect);
    }

    public static function getStatusEffectMaxDuration(string $status)
    {
        return match ($status)
        {
            StatusEffectEnum::CAFFEINATED => 8 * 60,
            StatusEffectEnum::EGGPLANT_CURSED, StatusEffectEnum::GLITTER_BOMBED => 48 * 60,
            StatusEffectEnum::HEX_HEXED => 6 * 60,
            default => 24 * 60,
        };
    }
}