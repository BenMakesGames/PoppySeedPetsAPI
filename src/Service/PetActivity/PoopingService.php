<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

// yep. this game has a class called "PoopingService". you're welcome.
class PoopingService
{
    private $inventoryService;
    private $responseService;
    private $squirrel3;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, Squirrel3 $squirrel3
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->squirrel3 = $squirrel3;
    }

    public function shed(Pet $pet)
    {
        $this->inventoryService->receiveItem($pet->getSpecies()->getSheds(), $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' shed this.', LocationEnum::HOME);

        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% shed some ' . $pet->getSpecies()->getSheds()->getName() . '.', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;
    }

    public function poopDarkMatter(Pet $pet)
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
        {
            $this->inventoryService->receiveItem('Dark Matter', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' ' . $this->squirrel3->rngNextFromArray([
                'pooped this. Yay?',
                'pooped this. Neat?',
                'pooped this. Yep.',
                'pooped this. Hooray. Poop.'
            ]), LocationEnum::HOME);
        }
        else
        {
            $this->inventoryService->receiveItem('Dark Matter', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' pooped this.', LocationEnum::HOME);
        }

        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name%, um, _created_ some Dark Matter.', 'items/element/dark-matter')
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;
    }
}
