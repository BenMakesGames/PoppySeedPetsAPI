<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\Pet;

class PetChanges
{
    public int $food;
    public int $safety;
    public int $love;
    public int $esteem;
    public int $exp;
    public int $level;
    public int $affection;
    public int $affectionLevel;
    public ?int $scrollLevel;

    public function __construct(Pet $pet)
    {
        $this->food = $pet->getFood();
        $this->safety = $pet->getSafety();
        $this->love = $pet->getLove();
        $this->esteem = $pet->getEsteem();
        $this->exp = $pet->getExperience();
        $this->level = $pet->getLevel();
        $this->affection = $pet->getAffectionPoints();
        $this->affectionLevel = $pet->getAffectionLevel();
        $this->scrollLevel = $pet->getSkills()->getScrollLevels();
    }

    public function compare(Pet $pet): PetChangesSummary
    {
        $food = $pet->getFood() - $this->food;
        $safety = $pet->getSafety() - $this->safety;
        $love = $pet->getLove() - $this->love;
        $esteem = $pet->getEsteem() - $this->esteem;
        $exp = $pet->getExperience() - $this->exp;
        $level = $pet->getLevel() - $this->level;
        $affection = $pet->getAffectionPoints() - $this->affection;
        $affectionLevel = $pet->getAffectionLevel() - $this->affectionLevel;
        $scrollLevel = $pet->getSkills()->getScrollLevels() - $this->scrollLevel;

        $summary = new PetChangesSummary();

        $summary->food = PetChangesSummary::rate($food);
        $summary->safety = PetChangesSummary::rate($safety);
        $summary->love = PetChangesSummary::rate($love);
        $summary->esteem = PetChangesSummary::rate($esteem);

        // don't report exp loss, because it only happens from gaining levels or skill scrolls
        $summary->exp = $exp > 0 ? PetChangesSummary::rate($exp) : null;

        $summary->level = PetChangesSummary::rate($level);
        $summary->affection = PetChangesSummary::rate($affection);
        $summary->affectionLevel = PetChangesSummary::rate($affectionLevel);
        $summary->scrollLevel = PetChangesSummary::rate($scrollLevel);

        return $summary;
    }
}
