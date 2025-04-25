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


namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/smilingWand")]
class SmilingWandController
{
    #[Route("/{inventory}/use", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function waveSmilingWand(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'smilingWand');

        $expressions = mb_trim($request->request->getString('expressions', ''));

        if(!self::validExpressions($expressions))
            throw new PSPFormValidationException('You must select three different expressions.');

        $petId = $request->request->getInt('pet', 0);

        /** @var Pet $pet */
        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user,
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        $pet->setAffectionExpressions($expressions);

        $em->remove($inventory);
        $em->flush();

        PetActivityLogFactory::createUnreadLog($em, $pet, ActivityHelpers::UserName($user, true) . ' waved a Smiling Wand over ' . ActivityHelpers::PetName($pet) . ', changing how they express themselves when pet!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    private static function validExpressions(string $expressions): bool
    {
        if(strlen($expressions) !== 3)
            return false;

        $seenExpressions = [];

        for($i = 0; $i < 3; $i++)
        {
            $char = $expressions[$i];

            if(in_array($char, $seenExpressions))
                return false;

            if($char < 'A' || $char > 'Z')
                return false;

            $seenExpressions[] = $char;
        }

        return true;
    }
}
