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
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Model\FilterResults;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/vault")]
class GetVaultContentsController
{
    private const int PAGE_SIZE = 50;

    #[Route("/contents", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getContents(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor, NormalizerInterface $normalizer
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::InfinityVault))
            throw new PSPNotUnlockedException('Infinity Vault');

        $vault = $user->getVault();
        if($vault === null || !$vault->isOpen())
            throw new PSPInvalidOperationException('The vault is closed.');

        $page = $request->query->getInt('page', 0);

        $qb = $em->createQueryBuilder()
            ->select('vi')->from(VaultInventory::class, 'vi')
            ->join('vi.item', 'item')
            ->andWhere('vi.user = :user')
            ->andWhere('vi.quantity > 0')
            ->setParameter('user', $user)
            ->orderBy('item.name', 'ASC')
        ;

        $paginator = new Paginator($qb);
        $resultCount = $paginator->count();
        $pageCount = max(1, (int)ceil($resultCount / self::PAGE_SIZE));
        $page = max(0, min($page, $pageCount - 1));

        $paginator->getQuery()
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE);

        $results = new FilterResults();
        $results->page = $page;
        $results->pageSize = self::PAGE_SIZE;
        $results->pageCount = $pageCount;
        $results->resultCount = $resultCount;
        /** @var VaultInventory[] $vaultItems */
        $vaultItems = iterator_to_array($paginator);
        $results->results = array_map(
            fn(VaultInventory $vi) => [
                'id' => (string)$vi->getId(),
                'itemId' => $vi->getItem()->getId(),
                'itemName' => $vi->getItem()->getName(),
                'itemImage' => $vi->getItem()->getImage(),
                'makerName' => $vi->getMaker()?->getName(),
                'quantity' => $vi->getQuantity(),
            ],
            $vaultItems
        );

        $data = $normalizer->normalize($results, null, ['groups' => [
            SerializationGroupEnum::FILTER_RESULTS,
        ]]);

        return $responseService->success($data);
    }
}

class VaultItemResponse
{
    public function __construct(
        public string $id,
        public int $itemId,
        public string $itemName,
        public string $itemImage,
        public ?string $makerName,
        public int $quantity,
    ) {}
}
