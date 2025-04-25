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


namespace App\Controller\MarketBid;

use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Repository\MarketBidRepository;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/marketBid")]
class DeleteBidController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{bidId}", methods: ["DELETE"], requirements: ["bidId" => "\d+"])]
    public function deleteBid(
        int $bidId, ResponseService $responseService, TransactionService $transactionService,
        MarketBidRepository $marketBidRepository, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();
        $bid = $marketBidRepository->find($bidId);

        if(!$bid || $bid->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('That bid could not be found (maybe someone else already sold you the item!)');

        $em->remove($bid);

        $transactionService->getMoney($user, $bid->getQuantity() * $bid->getBid(), 'Money refunded from canceling bid on ' . $bid->getQuantity() . 'x ' . $bid->getItem()->getName() . '.');

        return $responseService->success();
    }
}
