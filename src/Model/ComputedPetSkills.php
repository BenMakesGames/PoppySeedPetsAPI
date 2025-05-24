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


namespace App\Model;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\DateFunctions;
use Symfony\Component\Serializer\Attribute\Groups;

class ComputedPetSkills
{
    public function __construct(private readonly Pet $pet)
    {
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    /// ATTRIBUTES

    #[Groups(['myPet'])]
    public function getStrength(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getStrength();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::WONDROUS_STRENGTH) ? 2 : 0)
        ;

        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::Wereform) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Vivacious) ? 1 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getStamina(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getStamina();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::WONDROUS_STAMINA) ? 2 : 0) +
            (($this->pet->hasMerit(MeritEnum::MANXOME) && $this->pet->getSkills()->getDexterity() >= $this->pet->getSkills()->getStamina()) ? 1 : 0)
        ;

        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::Wereform) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Vivacious) ? 1 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getDexterity(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getDexterity();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::PREHENSILE_TONGUE) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::WONDROUS_DEXTERITY) ? 2 : 0) +
            (($this->pet->hasMerit(MeritEnum::MANXOME) && $this->pet->getSkills()->getDexterity() < $this->pet->getSkills()->getStamina()) ? 1 : 0)
        ;

        $skill->statusEffects = ($this->pet->hasStatusEffect(StatusEffectEnum::Vivacious) ? 1 : 0);

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getIntelligence(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getIntelligence();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::WONDROUS_INTELLIGENCE) ? 2 : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0)
        ;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::Wereform) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Tired) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Caffeinated) ? 2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Vivacious) ? 1 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getPerception(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getPerception();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::WONDROUS_PERCEPTION) ? 2 : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0)
        ;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::Tired) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Caffeinated) ? 2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Vivacious) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Spiced) ? 2 : 0)
        ;

        return $skill;
    }

    // SKILLS

    #[Groups(['myPet'])]
    public function getNature(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getNature();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->natureBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire) ? -3 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::HexHexed) ? 6 - $this->pet->getSkills()->getNature() : 0) +
            ($this->pet->getSkills()->getNature() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FocusedNature) ? 3 : 0)
        ;
        $skill->merits = ($this->pet->hasMerit(MeritEnum::GREEN_THUMB) ? 1 : 0);

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getBrawl(bool $allowRanged = true): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getBrawl();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->brawlBonus($allowRanged) : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::Wereform) ? 3 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire) ? 2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Rawr) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::HexHexed) ? 6 - $this->pet->getSkills()->getBrawl() : 0) +
            ($skill->base < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FocusedBrawl) ? 3 : 0)
        ;
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::WAY_OF_THE_EMPTY_HAND) && $skill->tool == 0 ? 5 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getStealth(): TotalPetSkill
    {
        $hasNoShadowOrReflection = $this->pet->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION);

        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getStealth();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->stealthBonus() : 0;
        $skill->merits =
            ($hasNoShadowOrReflection ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::SPECTRAL) ? 1 : 0)
        ;

        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire) ? 2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::HexHexed) ? 6 - $this->pet->getSkills()->getStealth() : 0) +
            ($this->pet->getSkills()->getStealth() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FocusedStealth) ? 3 : 0)
        ;

        if($this->pet->hasStatusEffect(StatusEffectEnum::Invisible))
        {
            $skill->statusEffects += ($hasNoShadowOrReflection ? 9 : 10);
        }

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getCrafts(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getCrafts();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->craftsBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HexHexed) ? 6 - $this->pet->getSkills()->getCrafts() : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::SilkInfused) ? 1 : 0) +
            ($this->pet->getSkills()->getCrafts() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FocusedCrafts) ? 3 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getArcana(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getArcana();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->arcanaBonus() : 0;
        $skill->merits = ($this->pet->hasMerit(MeritEnum::LUMINARY_ESSENCE) ? 1 : 0);
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HexHexed) ? 6 - $this->pet->getSkills()->getArcana() : 0) +
            (int)ceil($this->pet->getPsychedelic() * 5 / $this->pet->getMaxPsychedelic()) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::OutOfThisWorld) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Moonstruck) ? 10 : 0) +
            ($this->pet->getSkills()->getArcana() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FocusedArcana) ? 3 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getMusic(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getMusic();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->musicBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HexHexed) ? 6 - $this->pet->getSkills()->getMusic() : 0) +
            ($this->pet->getSkills()->getMusic() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FocusedMusic) ? 3 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getScience(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getScience();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->scienceBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HexHexed) ? 6 - $this->pet->getSkills()->getScience() : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::OutOfThisWorld) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Moonstruck) ? 10 : 0) +
            ($this->pet->getSkills()->getScience() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FocusedScience) ? 3 : 0)
        ;

        return $skill;
    }

    // ACTIVITY BONUSES

    #[Groups(['myPet'])]
    public function getFishingBonus(): TotalPetSkill
    {
        // no bonus for the casting no reflection merit; we grant that bonus elsewhere
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->fishingBonus() : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getSmithingBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->smithingBonus() : 0;
        $skill->statusEffects = $this->pet->hasStatusEffect(StatusEffectEnum::HotToTheTouch) ? 1 : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getGatheringBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->gatheringBonus() : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getClimbingBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->climbingBonus() : 0;
        $skill->statusEffects =
            (($this->pet->hasMerit(MeritEnum::PREHENSILE_TONGUE) && $this->pet->hasStatusEffect(StatusEffectEnum::AntiGravd)) ? 3 : 0) +
            $this->pet->hasStatusEffect(StatusEffectEnum::Hoppin) ? 1 : 0
        ;
        $skill->merits = ($this->pet->hasMerit(MeritEnum::GECKO_FINGERS) ? 2 : 0);

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getUmbraBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();

        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->umbraBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::Wereform) ? 3 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getMagicBindingBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();

        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->magicBindingBonus() : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getPhysicsBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->physicsBonus() : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getElectronicsBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->electronicsBonus() : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getHackingBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->hackingBonus() : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getMiningBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->miningBonus() : 0;
        $skill->merits = ($this->pet->hasMerit(MeritEnum::MORTARS_AND_PESTLES) ? 1 : 0);

        return $skill;
    }

    // MISC EFFECTS

    #[Groups(['myPet'])]
    public function getCanSeeInTheDark(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() && $this->pet->getTool()->providesLight() ? 1 : 0;
        $skill->merits = $this->pet->hasMerit(MeritEnum::DARKVISION) ? 1 : 0;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getHasProtectionFromHeat(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() && $this->pet->getTool()->protectsFromHeat() ? 1 : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HeatResistant) ? 1 : 0)
        ;

        return $skill;
    }

    #[Groups(['myPet'])]
    public function getHasProtectionFromElectricity(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::SHOCK_RESISTANT) ? 1 : 0)
        ;

        return $skill;
    }

    public function getSexDrive(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSexDrive();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->sexDriveBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::Wereform) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::Moonstruck) ? 2 : 0);

        return $skill;
    }
}
