<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Service\CommentFormatter;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/proboscis")]
class Proboscis extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
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

        ItemControllerHelpers::validateInventory($user, $inventory, 'proboscis');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);
        $skills = $pet->getComputedSkills();

        $sugarQuantity = 2 + (int)floor(($skills->getNature()->getTotal() + $skills->getDexterity()->getTotal()) / 2);
        $honeyCombQuantity = 0;

        if($sugarQuantity >= 4)
        {
            $honeyCombQuantity = $rng->rngNextInt(0, (int)($sugarQuantity / 2) - 1);
            $sugarQuantity -= $honeyCombQuantity * 2;
        }

        $actionDescription = "drank from the flowers of the island, and amassed {$sugarQuantity} Sugar";

        if($honeyCombQuantity > 0)
            $actionDescription .= ", and {$honeyCombQuantity} Honeycomb";

        $activityLog = PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name% {$actionDescription}!")
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        for($i = 0; $i < $sugarQuantity; $i++)
            $inventoryService->petCollectsItem('Sugar', $pet, "{$pet->getName()} used a Proboscis to drink from the flowers of the island, and got this!", $activityLog);

        for($i = 0; $i < $honeyCombQuantity; $i++)
            $inventoryService->petCollectsItem('Honeycomb', $pet, "{$pet->getName()} used a Proboscis to drink from the flowers of the island, and got this!", $activityLog);

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