<?php

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Repository\PetRepository;
use App\Service\CommentFormatter;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/molly")
 */
class Molly extends AbstractController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        EntityManagerInterface $em,
        InventoryService $inventoryService,
        PetExperienceService $petExperienceService,
        CommentFormatter $commentFormatter,
        IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'molly');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);
        $skills = $pet->getComputedSkills();

        $quantity = 2 + floor(($skills->getNature()->getTotal() + $skills->getDexterity()->getTotal()) / 3);

        $milkQuantity = $quantity < 4 ? 1 : $rng->rngNextInt(1, floor($quantity / 2));
        $fluffQuantity = max(1, $quantity - $milkQuantity);

        $loot = [];

        if($milkQuantity > 0)
            $loot[] = "{$milkQuantity}× Milk";

        if($fluffQuantity > 0)
            $loot[] = "{$fluffQuantity}× Fluff";

        $babies = $rng->rngNextInt(3, 5);
        $babyItem = $rng->rngNextBool() ? 'Catmouse Figurine' : 'Tentacat Figurine';

        $howMessyWasIt = $rng->rngNextFromArray([
            'a surprisingly-messy',
            'an astonishingly-shambolic',
            'an unbelievably-chaotic',
        ]);

        $actionDescription = "helped the Molly give birth to a litter of... {$babies} {$babyItem}s?? It was {$howMessyWasIt} affair, during which they collected " . ArrayFunctions::list_nice($loot) . "...";

        $activityLog = PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name% {$actionDescription}")
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        for($i = 0; $i < $milkQuantity; $i++)
            $inventoryService->petCollectsItem('Creamy Milk', $pet, "{$pet->getName()} collected this while helping a Molly \"give birth\" to some {$babyItem}s...", $activityLog);

        for($i = 0; $i < $fluffQuantity; $i++)
            $inventoryService->petCollectsItem('Fluff', $pet, "{$pet->getName()} collected this while helping a Molly \"give birth\" to some {$babyItem}s...", $activityLog);

        for($i = 0; $i < $babies; $i++)
            $inventoryService->petCollectsItem($babyItem, $pet, "{$pet->getName()} helped a Molly \"give birth\" to this...", $activityLog);

        $petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);

        $activityLog->setChanges($petChanges->compare($pet));

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            $commentFormatter->format($activityLog->getEntry()),
            [ 'itemDeleted' => true ]
        );
    }
}