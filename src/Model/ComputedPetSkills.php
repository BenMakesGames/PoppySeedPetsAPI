<?php
namespace App\Model;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\DateFunctions;
use Symfony\Component\Serializer\Annotation\Groups;

class ComputedPetSkills
{
    private $pet;

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
    public function getDexterity(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getDexterity();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::PREHENSILE_TONGUE) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::WONDROUS_DEXTERITY) ? 2 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getStrength(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getStrength();
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::MOON_BOUND) ? DateFunctions::moonStrength(new \DateTimeImmutable()) : 0) +
            ($this->pet->hasMerit(MeritEnum::WONDROUS_STRENGTH) ? 2 : 0)
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
            ($this->pet->hasMerit(MeritEnum::WONDROUS_STAMINA) ? 2 : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getIntelligence(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getIntelligence();
        $skill->merits = ($this->pet->hasMerit(MeritEnum::WONDROUS_INTELLIGENCE) ? 2 : 0);
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::TIRED) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::CAFFEINATED) ? 2 : 0)
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
        $skill->merits = ($this->pet->hasMerit(MeritEnum::WONDROUS_PERCEPTION) ? 2 : 0);
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::TIRED) ? -2 : 0) +
            ($this->pet->hasStatusEffect(StatusEffectEnum::CAFFEINATED) ? 2 : 0)
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
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getNature() : 0)
        ;

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
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getBrawl() : 0)
        ;

        return $skill;
    }

    /**
     * @Groups({"myPet"})
     */
    public function getStealth(): TotalPetSkill
    {
        $skill = new TotalPetSkill();
        $skill->base = $this->pet->getSkills()->getStealth();
        $skill->tool = $this->pet->getTool() ? $this->pet->getTool()->stealthBonus() : 0;
        $skill->merits =
            ($this->pet->hasMerit(MeritEnum::NO_SHADOW_OR_REFLECTION) ? 1 : 0) +
            ($this->pet->hasMerit(MeritEnum::SPECTRAL) ? 1 : 0)
        ;
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getStealth() : 0)
        ;

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
            ($this->pet->hasStatusEffect(StatusEffectEnum::SILK_INFUSED) ? 1 : 0)
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
        $skill->statusEffects =
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getUmbra() : 0) +
            ceil($this->pet->getPsychedelic() * 5 / $this->pet->getMaxPsychedelic())
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
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getMusic() : 0)
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
            ($this->pet->hasStatusEffect(StatusEffectEnum::HEX_HEXED) ? 6 - $this->pet->getSkills()->getScience() : 0)
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

        return $skill;
    }

    // summary of misc effects
}
