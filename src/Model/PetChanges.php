<?php
namespace App\Model;

use App\Entity\Pet;

class PetChanges
{
    private $food;
    private $safety;
    private $love;
    private $esteem;

    public function __construct(Pet $pet)
    {
        $this->food = $pet->getFood();
        $this->safety = $pet->getSafety();
        $this->love = $pet->getLove();
        $this->esteem = $pet->getEsteem();
        $this->experience = $pet->getExperience();
    }

    public function compare(Pet $pet): PetChangesSummary
    {
        $food = $pet->getFood() - $this->food;
        $safety = $pet->getSafety() - $this->safety;
        $love = $pet->getLove() - $this->love;
        $esteem = $pet->getEsteem() - $this->esteem;

        $summary = new PetChangesSummary();

        $summary->food = PetChangesSummary::rate($food);
        $summary->safety = PetChangesSummary::rate($safety);
        $summary->love = PetChangesSummary::rate($love);
        $summary->esteem = PetChangesSummary::rate($esteem);

        return $summary;
    }

}