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
    public const array Bites = [
        StatusEffectEnum::BittenByAVampire,
        StatusEffectEnum::BittenByAWerecreature
    ];

    public static function isImmuneToStatusEffect(Pet $pet, StatusEffectEnum $status): bool
    {
        $hasSilverblood = $pet->hasMerit(MeritEnum::SILVERBLOOD);
        $hasVampireBite = $pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire);

        if($status == StatusEffectEnum::Wereform && ($hasSilverblood || $hasVampireBite))
            return true;

        $statusIsABite = ArrayFunctions::any(self::Bites, fn($bite) => $bite == $status);

        if($statusIsABite)
        {
            $alreadyHasABite = ArrayFunctions::any(self::Bites, fn($bite) => $pet->hasStatusEffect($bite));
            $immuneToBites = $alreadyHasABite || $hasSilverblood;

            if($immuneToBites)
                return true;
        }

        return false;
    }

    public static function applyStatusEffect(EntityManagerInterface $em, Pet $pet, StatusEffectEnum $status, int $durationInMinutes): void
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

        if($status === StatusEffectEnum::Wereform)
        {
            /**
             * @var string[] $itemsDropped
             */
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
        else if(str_starts_with($status->value, 'Focused ('))
        {
            // the "Focused" family of status effects are mutually exclusive
            $statusEffectsToRemove = array_merge(
                $statusEffectsToRemove,
                array_filter($pet->getStatusEffects()->toArray(), fn(StatusEffect $se) => str_starts_with($se->getStatus()->value, 'Focused (') && $se->getStatus() !== $status)
            );
        }
        else if(str_starts_with($status->value, 'Fated ('))
        {
            // the "Fated" family of status effects are mutually exclusive
            $statusEffectsToRemove = array_merge(
                $statusEffectsToRemove,
                array_filter($pet->getStatusEffects()->toArray(), fn(StatusEffect $se) => str_starts_with($se->getStatus()->value, 'Fated (') && $se->getStatus() !== $status)
            );
        }

        foreach($statusEffectsToRemove as $statusEffect)
            $pet->removeStatusEffect($statusEffect);
    }

    public static function getStatusEffectMaxDuration(StatusEffectEnum $status): int
    {
        return match ($status)
        {
            StatusEffectEnum::Caffeinated => 8 * 60,
            StatusEffectEnum::EggplantCursed, StatusEffectEnum::GlitterBombed => 48 * 60,
            StatusEffectEnum::HexHexed => 6 * 60,
            default => 24 * 60,
        };
    }
}