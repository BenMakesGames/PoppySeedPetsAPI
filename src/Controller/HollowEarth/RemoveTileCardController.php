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


namespace App\Controller\HollowEarth;

use App\Entity\HollowEarthPlayerTile;
use App\Entity\HollowEarthTile;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/hollowEarth")]
class RemoveTileCardController
{
    #[Route("/removeTileCard", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function removeTileCard(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new PSPNotUnlockedException('Hollow Earth');

        if($player->getCurrentAction())
            throw new PSPInvalidOperationException('You can\'t change the map while you\'re moving!');

        $tileId = $request->request->getInt('tile', 0);

        $tile = $em->getRepository(HollowEarthTile::class)->find($tileId);

        if(!$tile)
            throw new PSPNotFoundException('That space in the Hollow Earth does not exist?!?! (Maybe reload and try again...)');

        if($tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed')
            throw new PSPInvalidOperationException('That space in the Hollow Earth cannot be changed!');

        $existingPlayerTile = $em->getRepository(HollowEarthPlayerTile::class)->findOneBy([
            'player' => $user,
            'tile' => $tile,
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setCard(null);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setCard(null)
            ;

            $em->persist($playerTile);
        }

        $em->flush();

        return $responseService->success();
    }
}
