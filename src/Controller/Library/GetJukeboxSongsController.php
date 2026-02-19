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

namespace App\Controller\Library;

use App\Entity\Library;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\JukeboxService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/library/jukebox")]
class GetJukeboxSongsController
{
    #[Route("/songs", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getJukeboxSongs(
        ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor, JukeboxService $jukeboxService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Library))
            throw new PSPNotUnlockedException('Library');

        $library = $user->getLibrary();

        if(!$library)
        {
            $library = new Library($user);
            $user->setLibrary($library);
            $em->persist($library);
            $em->flush();
        }

        if(!$library->getHasJukebox())
            throw new PSPInvalidOperationException('Your Library does not have a Jukebox installed.');

        if($user->getUnlockedSongs()->isEmpty())
        {
            $jukeboxService->unlockStartingSongs($user);
            $em->flush();
        }

        return $responseService->success($jukeboxService->getSongsAvailable($user));
    }
}
