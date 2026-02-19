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

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Library;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class InstallJukeboxController
{
    #[Route("/jukebox/{inventory}/install", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function install(
        Inventory $inventory, ResponseService $responseService,
        EntityManagerInterface $em, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'jukebox/#/install');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Library))
            throw new PSPNotUnlockedException('Library');

        $library = $user->getLibrary();

        if(!$library)
        {
            $library = new Library($user);
            $user->setLibrary($library);
            $em->persist($library);
        }

        if($library->getHasJukebox())
            return $responseService->itemActionSuccess('Your Library already has a Jukebox!');

        $em->remove($inventory);
        $library->setHasJukebox(true);
        $em->flush();

        return $responseService->itemActionSuccess('You install the Jukebox in your Library!', ['itemDeleted' => true]);
    }
}
