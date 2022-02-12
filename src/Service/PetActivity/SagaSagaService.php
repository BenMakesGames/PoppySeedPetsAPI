<?php

namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Repository\MeritRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class SagaSagaService
{
    private IRandom $rng;
    private InventoryService $inventoryService;
    private MeritRepository $meritRepository;
    private ResponseService $responseService;

    public function __construct(
        Squirrel3 $rng, InventoryService $inventoryService, MeritRepository $meritRepository,
        ResponseService $responseService
    )
    {
        $this->rng = $rng;
        $this->inventoryService = $inventoryService;
        $this->meritRepository = $meritRepository;
    }

    public function petCompletedSagaSaga(Pet $pet): bool
    {
        if(!$pet->hasMerit(MeritEnum::SAGA_SAGA))
            return false;

        $possibleSkills = [];

        if($pet->getSkills()->getStealth() >= 7) $possibleSkills[] = PetSkillEnum::STEALTH;
        if($pet->getSkills()->getNature() >= 7) $possibleSkills[] = PetSkillEnum::NATURE;
        if($pet->getSkills()->getBrawl() >= 7) $possibleSkills[] = PetSkillEnum::BRAWL;
        if($pet->getSkills()->getUmbra() >= 7) $possibleSkills[] = PetSkillEnum::UMBRA;
        if($pet->getSkills()->getCrafts() >= 7) $possibleSkills[] = PetSkillEnum::CRAFTS;
        if($pet->getSkills()->getMusic() >= 7) $possibleSkills[] = PetSkillEnum::MUSIC;
        if($pet->getSkills()->getScience() >= 7) $possibleSkills[] = PetSkillEnum::SCIENCE;

        if(count($possibleSkills) === 0)
            return false;

        $skill = $this->squirrel3->rngNextFromArray($possibleSkills);

        $this->inventoryService->petCollectsItem('Skill Scroll: ' . $skill, $pet, $pet->getName() . ' was transformed into this scroll!', null);

        $pet
            ->removeMerit($this->meritRepository->findOneByName(MeritEnum::SAGA_SAGA))
            ->removeMerit($this->meritRepository->findOneByName(MeritEnum::AFFECTIONLESS))
            ->addMerit($this->meritRepository->findOneByName(MeritEnum::SPECTRAL))
            ->setName('Ghost of ' . $pet->getName())
            ->resetAllNeeds()
            ->clearExp()
        ;

        $pet->getSkills()
            ->setStealth(0)
            ->setNature(0)
            ->setBrawl(0)
            ->setUmbra(0)
            ->setCrafts(0)
            ->setMusic(0)
            ->setScience(0)
        ;

        $pet->getHouseTime()
            ->setSocialEnergy(0)
            ->setActivityTime(0)
        ;

        $this->responseService->createActivityLog(
            $pet,
            ActivityHelpers::PetName($pet) . ' got 7 points in ' . $skill . ', and was transformed into a skill scroll! All that remains is their ghost...',
            ''
        );

        return true;
    }

}