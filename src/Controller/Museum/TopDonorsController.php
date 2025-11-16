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

use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPNotUnlockedException;
use App\Model\FilterResults;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/museum")]
class TopDonorsController
{
    #[Route("/topDonors", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getTopDonors(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        if(!$userAccessor->getUserOrThrow()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $qb = $em->getRepository(User::class)->createQueryBuilder('u')
            ->select('u AS user,s.value AS itemsDonated')
            ->leftJoin(UserStats::class, 's', Expr\Join::WITH, 's.user = u.id')
            ->andWhere('s.stat = :statName')
            ->addOrderBy('s.value', 'DESC')
            ->addOrderBy('s.lastTime', 'ASC')
            ->setParameter('statName', UserStat::ItemsDonatedToMuseum)
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

        return $responseService->success($results, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]);
    }
}
