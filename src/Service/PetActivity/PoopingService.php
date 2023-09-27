<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Repository\PetActivityLogTagRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

// yep. this game has a class called "PoopingService". you're welcome.
class PoopingService
{
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private IRandom $squirrel3;
    private EntityManagerInterface $em;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, IRandom $squirrel3,
        EntityManagerInterface $em
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->squirrel3 = $squirrel3;
        $this->em = $em;
    }

    public function shed(Pet $pet)
    {
        $this->inventoryService->receiveItem($pet->getSpecies()->getSheds(), $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' shed this.', LocationEnum::HOME);

        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% shed some ' . $pet->getSpecies()->getSheds()->getName() . '.', '')
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Shedding']))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;
    }

    public function poopDarkMatter(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name%, um, _created_ some Dark Matter.', 'items/element/dark-matter')
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Pooping']))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;

        if($this->squirrel3->rngNextInt(1, 20) === 1)
        {
            $this->inventoryService->petCollectsItem('Dark Matter', $pet, $pet->getName() . ' ' . $this->squirrel3->rngNextFromArray([
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
