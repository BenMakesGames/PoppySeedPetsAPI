<?php

namespace App\Controller\Item\ChooseAPet;

use App\Entity\Inventory;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Repository\PetRepository;
use App\Service\CommentFormatter;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/proboscis")
 */
class Proboscis extends ChooseAPetController
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
        CommentFormatter $commentFormatter,
        IRandom $rng
    )
    {
        $this->validateInventory($inventory, 'proboscis');

        $pet = $this->getPet($request, $petRepository);
        $skills = $pet->getComputedSkills();

        $sugarQuantity = 2 + floor(($skills->getNature()->getTotal() + $skills->getDexterity()->getTotal()) / 2);
        $honeyCombQuantity = 0;

        if($sugarQuantity >= 4)
        {
            $honeyCombQuantity = $rng->rngNextInt(0, $sugarQuantity / 2 - 1);
            $sugarQuantity -= $honeyCombQuantity * 2;
        }

        $actionDescription = "drank from the flowers of the island, and amassed {$sugarQuantity} Sugar";

        if($honeyCombQuantity > 0)
            $actionDescription .= ", and {$honeyCombQuantity} Honeycomb";

        $activityLog = $responseService->createActivityLog($pet, "%pet:{$pet->getId()}.name% {$actionDescription}!", '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        for($i = 0; $i < $sugarQuantity; $i++)
            $inventoryService->petCollectsItem('Sugar', $pet, "{$pet->getName()} used a Proboscis to drink from the flowers of the island, and got this!", $activityLog);

        for($i = 0; $i < $honeyCombQuantity; $i++)
            $inventoryService->petCollectsItem('Honeycomb', $pet, "{$pet->getName()} used a Proboscis to drink from the flowers of the island, and got this!", $activityLog);

        if(!$petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog))
            $activityLog->setViewed();

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            $commentFormatter->format($activityLog->getEntry()),
            [ 'itemDeleted' => true ]
        );
    }
}