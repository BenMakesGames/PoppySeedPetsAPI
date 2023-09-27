<?php

namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PlayerLogHelpers;
use App\Functions\UserFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class KappaService
{
    private IRandom $rng;
    private PetExperienceService $petExperienceService;
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private EntityManagerInterface $em;
    private UserStatsRepository $userStatsRepository;

    public function __construct(
        IRandom $rng, PetExperienceService $petExperienceService, ResponseService $responseService,
        InventoryService $inventoryService, EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $this->rng = $rng;
        $this->petExperienceService = $petExperienceService;
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function doHuntKappa(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $totalSkill =
            $petWithSkills->getBrawl(false)->getTotal() +
            $petWithSkills->getStrength()->getTotal() +
            $petWithSkills->getDexterity()->getTotal();

        $this->em->remove($pet->getTool());
        $pet->setTool(null);

        if($totalSkill >= 12)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . ActivityHelpers::PetName($pet) . ' was thinking about what to do, a Kappa jumped them! ' . ActivityHelpers::PetName($pet) . ' saw it coming a mile away, though, beat the creature back, and reclaimed its stolen Shirikodama. (Their Cucumber was reduced to a pulp in the process.)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting', 'Adventure!' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);

            $this->inventoryService->petCollectsItem('Shirikodama', $pet, $pet->getName() . ' reclaimed this from a Kappa.', $activityLog);
        }
        else if($this->rng->rngNextInt(1, 20 + $totalSkill) >= 16)
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . ActivityHelpers::PetName($pet) . ' was thinking about what to do, a Kappa jumped them! It was a tough fight, but ' . ActivityHelpers::PetName($pet) . ' beat the creature back, and reclaimed its stolen Shirikodama! (Their Cucumber was reduced to a pulp in the process.)', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting', 'Adventure!' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            $this->inventoryService->petCollectsItem('Shirikodama', $pet, $pet->getName() . ' reclaimed this from a Kappa.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, 'While ' . ActivityHelpers::PetName($pet) . ' was thinking about what to do, a Kappa jumped them! It was a tough fight, which ended when the Kappa ate ' . ActivityHelpers::PetName($pet) . '\'s Cucumber, and ran off giggling! >:(', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting', 'Adventure!' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    public function doReturnShirikodama(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $skills =
            $petWithSkills->getPerception()->getTotal() +
            $petWithSkills->getIntelligence()->getTotal() +
            $petWithSkills->getPet()->getExtroverted() * 3;

        if($skills >= 5)
        {
            $owner = UserFunctions::findOneRecentlyActive($this->em, $pet->getOwner(), 72);

            if(!$owner)
                throw new PSPNotFoundException('Hm... there\'s no one to return it to! (I guess no one\'s been playing Poppy Seed Pets...)');

            $this->em->remove($pet->getTool());
            $pet->setTool(null);

            $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' recognized the Shirikodama as belonging to ' . ActivityHelpers::UserName($owner) . ', so returned it to them. ' . ActivityHelpers::UserName($owner) . ' thanked ' . ActivityHelpers::PetName($pet) . ' with many pets and pats.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
            ;
            $pet->increaseLove(4)->increaseEsteem(4);
            $this->petExperienceService->gainAffection($pet, 2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);

            $this->userStatsRepository->incrementStat($owner, UserStatEnum::PETTED_A_PET, 1);

            PlayerLogHelpers::create($this->em, $owner, ActivityHelpers::PetName($pet) . ' returned your Shirikodama! (Some Kappa must have stolen it!) You thank ' . ActivityHelpers::PetName($pet) . ' with pets and pats before swallowing the Shirikodama.', [
                'Shirikodama',
            ]);

            $this->userStatsRepository->incrementStat($pet->getOwner(), 'Returned a Shirikodama', 1);
        }
        else if($this->rng->rngNextInt(1, 3) > 1)
        {
            $owner = UserFunctions::findOneRecentlyActive($this->em, $pet->getOwner(), 72);

            if(!$owner)
                throw new PSPNotFoundException('Hm... there\'s no one to return it to! (I guess no one\'s been playing Poppy Seed Pets...)');

            $this->em->remove($pet->getTool());
            $pet->setTool(null);

            $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' wasn\'t immediately sure who the Shirikodama belonged to, so wandered the town for a little before spotting ' . ActivityHelpers::UserName($owner) . ', and recognizing them as the owner! ' . ActivityHelpers::PetName($pet) . ' returned the Shirikodama to ' . ActivityHelpers::UserName($owner) . ', who thanked them with many pets and pats.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
            ;
            $pet->increaseLove(4)->increaseEsteem(4);
            $this->petExperienceService->gainAffection($pet, 2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

            $this->userStatsRepository->incrementStat($owner, UserStatEnum::PETTED_A_PET, 1);

            PlayerLogHelpers::create($this->em, $owner, ActivityHelpers::PetName($pet) . ' returned your Shirikodama! (Some Kappa must have stolen it!) You thank ' . ActivityHelpers::PetName($pet) . ' with pets and pats before swallowing the Shirikodama.', [
                'Shirikodama',
            ]);

            $this->userStatsRepository->incrementStat($pet->getOwner(), 'Returned a Shirikodama', 1);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' wasn\'t sure who the Shirikodama belonged to, so wandered the town for a little. They approached several residents, but none were the owner.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
            ;
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        }

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }
}