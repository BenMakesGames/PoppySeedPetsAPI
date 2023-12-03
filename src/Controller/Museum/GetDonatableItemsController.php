<?php
namespace App\Controller\Museum;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Model\FilterResults;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/museum")]
class GetDonatableItemsController extends AbstractController
{
    #[Route("/donatable", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getDonatable(
        ResponseService $responseService, Request $request, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $qb = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->leftJoin('i.item', 'item')
            ->andWhere('i.location IN (:locations)')
            ->andWhere('item.id NOT IN (SELECT miitem.id FROM App\\Entity\\MuseumItem mi LEFT JOIN mi.item miitem WHERE mi.user=:user)')
            ->setParameter('locations', [ LocationEnum::HOME, LocationEnum::BASEMENT ])
            ->setParameter('user', $user)
            ->addGroupBy('item.id')
            ->addGroupBy('i.enchantment')
            ->addOrderBy('item.name')
            ->addOrderBy('i.enchantment')
        ;

        $paginator = new Paginator($qb);

        $resultCount = $paginator->count();
        $lastPage = ceil($resultCount / 20);
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
        $results->query = [ 'sql ' => $paginator->getQuery()->getSQL(), 'parameters' => $paginator->getQuery()->getParameters()->toArray() ];

        return $responseService->success($results, [
            SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_INVENTORY, SerializationGroupEnum::MY_DONATABLE_INVENTORY
        ]);
    }
}
