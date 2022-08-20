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
 * @Route("/item/nightAndDay")
 */
class NightAndDay extends ChooseAPetController
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
        $this->validateInventory($inventory, 'nightAndDay/#');

        $pet = $this->getPet($request, $petRepository);

        $pairOfItems = $rng->rngNextFromArray([
            [ 'Black Baabble', 'White Baabble' ],
            [ 'Black Feathers', 'White Feathers' ],
            [ 'Black Flag', 'White Flag' ],
        ]);

        $subject = $rng->rngNextFromArray([
            'on the duality of night and day; light and dark',
            'on their place in the infinite multiverse',
            'inward'
        ]);

        $messageMiddle = "focused {$subject}, and the {$inventory->getFullItemName()} turned into";
        $itemList = ArrayFunctions::list_nice($pairOfItems);

        $activityLog = $responseService->createActivityLog($pet, "%pet:{$pet->getId()}% $messageMiddle {$itemList}!", '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        foreach($pairOfItems as $item)
            $inventoryService->petCollectsItem($item, $pet, "{$pet->getName()} {$messageMiddle} this!", $activityLog);

        if(!$petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog))
            $activityLog->setViewed();

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            "{$pet->getName()} {$messageMiddle} {$itemList}!",
            [ 'itemDeleted' => true ]
        );
    }
}