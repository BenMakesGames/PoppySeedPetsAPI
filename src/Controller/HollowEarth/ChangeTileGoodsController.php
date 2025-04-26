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
use App\Exceptions\PSPInvalidOperationException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/hollowEarth")]
class ChangeTileGoodsController
{
    #[Route("/changeTileGoods", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changeTileGoods(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $player = $user->getHollowEarthPlayer();

        $selectedGoods = $request->request->getAlpha('goods');

        $tile = $player->getCurrentTile();

        if($tile->getGoods() === null || count($tile->getGoods()) === 0)
            throw new PSPInvalidOperationException('You are not on a tile that produces goods.');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('You can\'t change goods while you\'re moving!');

        if(!in_array($selectedGoods, $tile->getGoods()))
            throw new PSPInvalidOperationException('This tile is not capable of producing that type of good.');

        $existingPlayerTile = $em->getRepository(HollowEarthPlayerTile::class)->findOneBy([
            'player' => $user,
            'tile' => $tile->getId(),
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setGoods($selectedGoods);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setGoods($selectedGoods)
                ->setCard($tile->getCard())
            ;

            $em->persist($playerTile);
        }

        $em->flush();

        return $responseService->success();
    }
}
