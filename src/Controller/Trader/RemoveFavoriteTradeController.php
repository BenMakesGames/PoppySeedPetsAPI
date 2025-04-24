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


namespace App\Controller\Trader;

use App\Entity\User;
use App\Entity\UserFavoriteTrade;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\UserQuestRepository;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/trader")]
class RemoveFavoriteTradeController extends AbstractController
{
    #[Route("/{id}/favorite", methods: ["DELETE"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeExchange(
        string $id, TraderService $traderService, ResponseService $responseService, EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $exchange = $traderService->getOfferById($user, $id);

        if(!$exchange)
            throw new PSPNotFoundException('There is no such exchange available.');

        $favorite = $em->getRepository(UserFavoriteTrade::class)->findOneBy([
            'user' => $user,
            'trade' => $exchange->id
        ]);

        if($favorite)
        {
            $em->remove($favorite);
            $em->flush();
        }

        return $responseService->success();
    }
}
