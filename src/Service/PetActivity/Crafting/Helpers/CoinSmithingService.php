<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Item;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Repository\PetActivityLogTagRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;

class CoinSmithingService
{
    private $petExperienceService;
    private $transactionService;
    private $responseService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        PetExperienceService $petExperienceService, Squirrel3 $squirrel3, HouseSimService $houseSimService,
        TransactionService $transactionService, ResponseService $responseService,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->responseService = $responseService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    public function spillGold(ComputedPetSkills $petWithSkills, Item $triedToMake): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        $pet->increaseEsteem(-1);
        $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 8));
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ', but they accidentally burned themselves! :(', 'icons/activity-logs/burn')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing' ]))
        ;
    }

    public function makeSilverCoins(ComputedPetSkills $petWithSkills, Item $triedToMake)
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(75, 90), PetActivityStatEnum::SMITH, true);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $this->houseSimService->getState()->loseItem('Silver Bar', 1);

        $moneys = $this->squirrel3->rngNextInt(10, 20);
        $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' made some silver coins after failing to forge ' . $triedToMake->getNameWithArticle() . '.');
        $pet->increaseFood(-1);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ' from a Silver Bar, but spilled some of the silver, and almost burned themselves! They used the leftovers to make ' . $moneys . '~~m~~ worth of silver coins, instead.', 'icons/activity-logs/moneys')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', 'Moneys' ]))
        ;
    }

    public function makeGoldCoins(ComputedPetSkills $petWithSkills, Item $triedToMake)
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(75, 90), PetActivityStatEnum::SMITH, true);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $this->houseSimService->getState()->loseItem('Gold Bar', 1);

        $moneys = $this->squirrel3->rngNextInt(20, 30);
        $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' tried to forge ' . $triedToMake->getNameWithArticle() . ', but couldn\'t get the shape right, so just made gold coins, instead.');
        $pet->increaseFood(-1);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ' from a Gold Bar, but spilled some of the gold, and almost burned themselves! They used the leftovers to make ' . $moneys . '~~m~~ worth of gold coins, instead.', 'icons/activity-logs/moneys')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Smithing', 'Moneys' ]))
        ;
    }
}
