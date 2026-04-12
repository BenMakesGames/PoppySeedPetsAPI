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

namespace App\Controller\Vault;

use App\Entity\VaultInventory;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotUnlockedException;
use App\Enum\UserStat;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/vault")]
class OpenVaultController
{
    #[Route("/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openVault(
        ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor, TransactionService $transactionService,
        UserStatsService $userStatsService
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::InfinityVault))
            throw new PSPNotUnlockedException('Infinity Vault');

        $vault = $user->getVault();
        if($vault === null)
            throw new PSPInvalidOperationException('Vault not found.');

        if($vault->isOpen())
            throw new PSPInvalidOperationException('The vault is already open!');

        $itemCount = (int)$em->createQueryBuilder()
            ->select('SUM(vi.quantity)')->from(VaultInventory::class, 'vi')
            ->andWhere('vi.user = :user')
            ->andWhere('vi.quantity > 0')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $cost = 10 + (int)floor($itemCount / 10);

        if($user->getMoneys() < $cost)
            throw new PSPNotEnoughCurrencyException($cost . '~~m~~', $user->getMoneys() . '~~m~~');

        $transactionService->spendMoney($user, $cost, 'Opened the Infinity Vault for 1 hour.');
        $userStatsService->incrementStat($user, UserStat::OpenedTheInfinityVault);
        $userStatsService->incrementStat($user, UserStat::MoneysSpentOnTheInfinityVault, $cost);

        $vault->setOpenUntil(new \DateTimeImmutable()->modify('+1 hour'));

        $em->flush();

        return $responseService->success([
            'vaultOpenUntil' => $vault->getOpenUntil()->format('c'),
        ]);
    }
}
