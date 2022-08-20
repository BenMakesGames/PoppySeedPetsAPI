<?php

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/item/molly")
 */
class Molly extends ChooseAPetController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        PetRepository $petRepository,
        EntityManagerInterface $em,
        InventoryService $inventoryService,
        PetExperienceService $petExperienceService,
        IRandom $rng
    )
    {
        $this->validateInventory($inventory, 'molly/#');

        $pet = $this->getPet($request, $petRepository);
        $skills = $pet->getComputedSkills();

        $quantity = 2 + floor(($skills->getNature() + $skills->getDexterity()) / 3);

        $milkQuantity = $quantity < 4 ? 1 : $rng->rngNextInt(1, floor($quantity / 2));
        $fluffQuantity = max(1, $quantity - $milkQuantity);

        $loot = [];

        if($milkQuantity > 0)
            $loot[] = "{$milkQuantity}× Milk";

        if($fluffQuantity > 0)
            $loot[] = "{$fluffQuantity}× Fluff";

        $babies = $rng->rngNextInt(3, 5);
        $babyItem = $rng->rngNextBool() ? 'Catmouse Figurine' : 'Tentacat Figurine';

        $actionDescription = "helped the Molly give birth to a litter of... {$babies} {$babyItem}s?? It was a surprisingly-messy affair, during which they collected " . ArrayFunctions::list_nice($loot) . "...";

        $activityLog = $responseService->createActivityLog($pet, "%pet:{$pet->getId()}% $actionDescription!", '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        for($i = 0; $i < $milkQuantity; $i++)
            $inventoryService->petCollectsItem('Milk', $pet, "{$pet->getName()} collected this while helping a Molly \"give birth\" to some {$babyItem}s...", $activityLog);

        for($i = 0; $i < $fluffQuantity; $i++)
            $inventoryService->petCollectsItem('Fluff', $pet, "{$pet->getName()} collected this while helping a Molly \"give birth\" to some {$babyItem}s...", $activityLog);

        for($i = 0; $i < $babies; $i++)
            $inventoryService->petCollectsItem($babyItem, $pet, "{$pet->getName()} helped a Molly \"give birth\" to this...", $activityLog);

        if(!$petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog))
            $activityLog->setViewed();

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            "{$pet->getName()} {$actionDescription}!",
            [ 'itemDeleted' => true ]
        );
    }
}