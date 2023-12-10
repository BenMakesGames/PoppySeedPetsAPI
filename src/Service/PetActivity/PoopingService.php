<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\PetActivityLogTagHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

// yep. this game has a class called "PoopingService". you're welcome.
class PoopingService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly ResponseService $responseService,
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function shed(Pet $pet)
    {
        $this->inventoryService->receiveItem($pet->getSpecies()->getSheds(), $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' shed this.', LocationEnum::HOME);

        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% shed some ' . $pet->getSpecies()->getSheds()->getName() . '.', '')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Shedding']))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;
    }

    public function poopDarkMatter(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name%, um, _created_ some Dark Matter.', 'items/element/dark-matter')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Pooping']))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;

        if($this->rng->rngNextInt(1, 20) === 1)
        {
            $this->inventoryService->petCollectsItem('Dark Matter', $pet, $pet->getName() . ' ' . $this->rng->rngNextFromArray([
                'pooped this. Yay?',
                'pooped this. Neat?',
                'pooped this. Yep.',
                'pooped this. Hooray. Poop.'
            ]), $activityLog);
        }
        else
        {
            $this->inventoryService->petCollectsItem('Dark Matter', $pet, $pet->getName() . ' pooped this.', $activityLog);
        }

        return $activityLog;
    }
}
