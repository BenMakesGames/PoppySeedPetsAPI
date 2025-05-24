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


namespace App\Controller\Museum;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Model\FilterResults;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/museum")]
class GetDonatableItemsController
{
    #[Route("/donatable", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getDonatable(
        ResponseService $responseService, Request $request, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $qb = $em->createQueryBuilder()
            ->select('i')->from(Inventory::class, 'i')
            ->andWhere('i.owner=:user')
            ->leftJoin('i.item', 'item')
            ->andWhere('i.location IN (:locations)')
            ->andWhere('item.id NOT IN (SELECT miitem.id FROM App\\Entity\\MuseumItem mi LEFT JOIN mi.item miitem WHERE mi.user=:user)')
            ->setParameter('locations', [ LocationEnum::Home, LocationEnum::Basement ])
            ->setParameter('user', $user)
            ->addGroupBy('item.id')
            ->addGroupBy('i.enchantment')
            ->addOrderBy('item.name')
            ->addOrderBy('i.enchantment')
        ;

        $paginator = new Paginator($qb);

        $resultCount = $paginator->count();
        $lastPage = (int)ceil($resultCount / 20);
        $page = $request->query->getInt('page', 0);

        if($page < 0)
            $page = 0;
        else if($lastPage > 0 && $page >= $lastPage)
            $page = $lastPage - 1;

        $paginator->getQuery()
            ->setFirstResult($page * 20)
            ->setMaxResults(20)
        ;

        $results = new FilterResults();

        $results->page = $page;
        $results->pageSize = 20;
        $results->pageCount = $lastPage;
        $results->resultCount = $resultCount;
        $results->results = $paginator->getQuery()->execute();

        return $responseService->success($results, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::MY_INVENTORY,
            SerializationGroupEnum::MY_DONATABLE_INVENTORY
        ]);
    }
}
