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
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/vault")]
class GetVaultStatusController
{
    #[Route("/status", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getStatus(
        ResponseService $responseService, EntityManagerInterface $em, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::InfinityVault))
            throw new PSPNotUnlockedException('Infinity Vault');

        $vault = $user->getVault();
        if($vault === null)
            throw new PSPInvalidOperationException('Vault not found.');

        $itemCount = (int)$em->createQueryBuilder()
            ->select('SUM(vi.quantity)')->from(VaultInventory::class, 'vi')
            ->andWhere('vi.user = :user')
            ->andWhere('vi.quantity > 0')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $isOpen = $vault->isOpen();
        $openCost = 10 + floor($itemCount / 10);

        return $responseService->success([
            'isOpen' => $isOpen,
            'vaultOpenUntil' => $vault->getOpenUntil()->format('c'),
            'itemCount' => $itemCount,
            'openCost' => $openCost,
        ]);
    }
}

class GetVaultStatusResponse
{
    public function __construct(
        public bool $isOpen,
        public string $vaultOpenUntil,
        public int $itemCount,
        public int $openCost,
    ) {}
}
