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


namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\MuseumItem;
use App\Entity\Pet;
use App\Entity\User;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/scrollOfIllusions")]
class ApplyIllusionController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buyIllusion(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        Request $request,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'scrollOfIllusions');

        $petId = $request->request->getInt('petId');
        $illusionId = $request->request->getInt('illusionId');

        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user,
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        if(!$pet->getTool())
            throw new PSPInvalidOperationException('This pet does not have a tool equipped.');

        // verify that the user has donated the illusionId in question
        $donation = $em->getRepository(MuseumItem::class)->findOneBy([
            'user' => $user,
            'item' => $illusionId,
        ]);

        if(!$donation)
            throw new PSPNotFoundException('You have not donated one of those to the Museum...');

        $pet->getTool()->setIllusion($donation->getItem());

        $em->remove($inventory);

        $em->flush();

        $responseService->setReloadInventory();
        $responseService->setReloadPets();

        return $responseService->success();
    }
}