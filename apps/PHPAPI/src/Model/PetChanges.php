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
