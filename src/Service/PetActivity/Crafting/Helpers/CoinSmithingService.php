<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Item;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ComputedPetSkills;
use App\Service\HouseSimService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

class CoinSmithingService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly HouseSimService $houseSimService,
        private readonly TransactionService $transactionService,
        private readonly ResponseService $responseService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function spillGold(ComputedPetSkills $petWithSkills, Item $triedToMake): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $pet->increaseEsteem(-1);
        $pet->increaseSafety(-$this->rng->rngNextInt(2, 8));

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ', but they accidentally burned themselves! :(', 'icons/activity-logs/burn')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

        return $activityLog;
    }

    public function makeSilverCoins(ComputedPetSkills $petWithSkills, Item $triedToMake)
    {
        $pet = $petWithSkills->getPet();

        $this->houseSimService->getState()->loseItem('Silver Bar', 1);

        $moneys = $this->rng->rngNextInt(10, 20);
        $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' made some silver coins after failing to forge ' . $triedToMake->getNameWithArticle() . '.');
        $pet->increaseFood(-1);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ' from a Silver Bar, but spilled some of the silver, and almost burned themselves! They used the leftovers to make ' . $moneys . '~~m~~ worth of silver coins, instead.', 'icons/activity-logs/moneys')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Moneys' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(75, 90), PetActivityStatEnum::SMITH, true);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::MINTED_MONEYS, $activityLog);

        return $activityLog;
    }

    public function makeGoldCoins(ComputedPetSkills $petWithSkills, Item $triedToMake)
    {
        $pet = $petWithSkills->getPet();

        $this->houseSimService->getState()->loseItem('Gold Bar', 1);

        $moneys = $this->rng->rngNextInt(20, 30);
        $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' tried to forge ' . $triedToMake->getNameWithArticle() . ', but couldn\'t get the shape right, so just made gold coins, instead.');
        $pet->increaseFood(-1);

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ' from a Gold Bar, but spilled some of the gold, and almost burned themselves! They used the leftovers to make ' . $moneys . '~~m~~ worth of gold coins, instead.', 'icons/activity-logs/moneys')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Moneys' ]))
        ;

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(75, 90), PetActivityStatEnum::SMITH, true);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::MINTED_MONEYS, $activityLog);

        return $activityLog;
    }
}
