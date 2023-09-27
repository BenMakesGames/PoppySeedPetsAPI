<?php
namespace App\Model;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\DateFunctions;
use Symfony\Component\Serializer\Annotation\Groups;

class ComputedPetSkills
{
    private Pet $pet;

    public function __construct(Pet $pet)
    {
        $this->pet = $pet;
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    /// ATTRIBUTES

    /**
     * @Groups({"myPet"})
     */
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
            ($this->pet->hasStatusEffect(StatusEffectEnum::WEREFORM) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::VIVACIOUS) ? 1 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getStamina(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getStamina();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::WONDROUS_STAMINA) ? 2 : 0)
        ;

        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::WEREFORM) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::VIVACIOUS) ? 1 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getDexterity(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getDexterity();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::PREHENSILE_TONGUE) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::WONDROUS_DEXTERITY) ? 2 : 0)
        ;

        $skill->statusEffects = ($this->pet->hasStatusEffect(StatusEffectEnum::VIVACIOUS) ? 1 : 0);

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getIntelligence(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getIntelligence();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::WONDROUS_INTELLIGENCE) ? 2 : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0)
        ;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::WEREFORM) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::TIRED) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::CAFFEINATED) ? 2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::VIVACIOUS) ? 1 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getPerception(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getPerception();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::WONDROUS_PERCEPTION) ? 2 : 0) +
            ($this->pet->hasMerit(MeritEnum::ETERNAL) ? 1 : 0)
        ;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::TIRED) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::CAFFEINATED) ? 2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::VIVACIOUS) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::SPICED) ? 2 : 0)
        ;

        return $skill;
    }

    // SKILLS

    /**
     * @Groups({"myPet"})
     */
    public function getNature(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getNature();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->natureBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getNature() : 0) +
            ($this->pet->getSkills()->getNature() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FOCUSED_NATURE) ? 3 : 0)
        ;
        $skill->merits = ($this->pet->hasMerit(MeritEnum::GREEN_THUMB) ? 1 : 0);

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getBrawl($allowRanged = true): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getBrawl();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->brawlBonus($allowRanged) : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::WEREFORM) ? 3 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::RAWR) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getBrawl() : 0) +
            ($skill->base < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FOCUSED_BRAWL) ? 3 : 0)
        ;
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::WAY_OF_THE_EMPTY_HAND) && $skill->tool == 0 ? 5 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
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
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getStealth() : 0) +
            ($this->pet->getSkills()->getStealth() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FOCUSED_STEALTH) ? 3 : 0)
        ;

        if($this->pet->hasStatusEffect(StatusEffectEnum::INVISIBLE))
        {
            $skill->statusEffects += ($hasNoShadowOrReflection ? 9 : 10);
        }

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getCrafts(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getCrafts();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->craftsBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getCrafts() : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::SILK_INFUSED) ? 1 : 0) +
            ($this->pet->getSkills()->getCrafts() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FOCUSED_CRAFTS) ? 3 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getUmbra(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getUmbra();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->umbraBonus() : 0;
        $skill->merits = ($this->pet->hasMerit(MeritEnum::LUMINARY_ESSENCE) ? 1 : 0);
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::WEREFORM) ? 3 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getUmbra() : 0) +
            ceil($this->pet->getPsychedelic() * 5 / $this->pet->getMaxPsychedelic()) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::OUT_OF_THIS_WORLD) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::MOONSTRUCK) ? 10 : 0) +
            ($this->pet->getSkills()->getUmbra() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FOCUSED_UMBRA) ? 3 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getMusic(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getMusic();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->musicBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getMusic() : 0) +
            ($this->pet->getSkills()->getMusic() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FOCUSED_MUSIC) ? 3 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getScience(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getScience();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->scienceBonus() : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getScience() : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::OUT_OF_THIS_WORLD) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::MOONSTRUCK) ? 10 : 0) +
            ($this->pet->getSkills()->getScience() < 10 && $this->pet->hasStatusEffect(StatusEffectEnum::FOCUSED_SCIENCE) ? 3 : 0)
        ;

        return $skill;
    }

    // ACTIVITY BONUSES

    /**
     * @Groups({"myPet"})
     */
    public function getFishingBonus(): TotalPetSkill
    {
        // no bonus for the casting no reflection merit; we grant that bonus elsewhere
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->fishingBonus() : 0;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getSmithingBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->smithingBonus() : 0;
        $skill->statusEffects = $this->pet->hasStatusEffect(StatusEffectEnum::HOT_TO_THE_TOUCH) ? 1 : 0;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getGatheringBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->gatheringBonus() : 0;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getClimbingBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->climbingBonus() : 0;
        $skill->statusEffects =
            (($this->pet->hasMerit(MeritEnum::PREHENSILE_TONGUE) && $this->pet->hasStatusEffect(StatusEffectEnum::ANTI_GRAVD)) ? 3 : 0) +
            $this->pet->hasStatusEffect(StatusEffectEnum::HOPPIN) ? 1 : 0
        ;
        $skill->merits = ($this->pet->hasMerit(MeritEnum::GECKO_FINGERS) ? 2 : 0);

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getExploreUmbraBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getMagicBindingBonus(): TotalPetSkill
    {
        $skill = new TotalPetSkill();

        return $skill;
    }

    // MISC EFFECTS

    /**
     * @Groups({"myPet"})
     */
    public function getCanSeeInTheDark(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() && $this->pet->getTool()->providesLight() ? 1 : 0;
        $skill->merits = $this->pet->hasMerit(MeritEnum::DARKVISION) ? 1 : 0;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getHasProtectionFromHeat(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->tool = $this->pet->getTool() && $this->pet->getTool()->protectsFromHeat() ? 1 : 0;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEAT_RESISTANT) ? 1 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
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
            ($this->pet->hasStatusEffect(StatusEffectEnum::WEREFORM) ? 1 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::MOONSTRUCK) ? 2 : 0);

        return $skill;
    }
}
