<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Model\FilterResults;
use App\Repository\InventoryRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\ItemFilterService;
use App\Service\Filter\MuseumFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/museum")
 */
class MuseumController extends PoppySeedPetsController
{
    /**
     * @Route("/{user}/items", methods={"GET"}, requirements={"user"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function userDonatedItems(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        if($this->getUser()->getUnlockedMuseum() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]
        );
    }

    /**
     * @Route("/{user}/nonItems", methods={"GET"}, requirements={"user"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function userNonDonatedItems(
        User $user,
        Request $request, ResponseService $responseService, ItemFilterService $itemFilterService
    )
    {
        if($this->getUser()->getUnlockedMuseum() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $itemFilterService->addRequiredFilter('notDonatedBy', $user->getId());

        return $responseService->success(
            $itemFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]
        );
    }

    /**
     * @Route("/{user}/itemCount", methods={"GET"}, requirements={"user"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function userItemCount(
        User $user,
        Request $request, ResponseService $responseService, MuseumFilterService $museumFilterService
    )
    {
        if($this->getUser()->getUnlockedMuseum() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $museumFilterService->addRequiredFilter('user', $user->getId());

        return $responseService->success(
            $museumFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]
        );
    }

    /**
     * @Route("/donatable", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getDonatable(
        ResponseService $responseService, Request $request, InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedMuseum() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $qb = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.id NOT IN (SELECT miitem.id FROM App\\Entity\\MuseumItem mi LEFT JOIN mi.item miitem WHERE mi.user=:user)')
            ->setParameter('user', $user)
            ->groupBy('item.id')
            ->orderBy('item.name', 'ASC')
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

        return $responseService->success($results, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_INVENTORY ]);
    }

    /**
     * @Route("/topDonors", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getTopDonors(
        Request $request, ResponseService $responseService, UserRepository $userRepository, NormalizerInterface $normalizer
    )
    {
        if($this->getUser()->getUnlockedMuseum() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $qb = $userRepository->createQueryBuilder('u')
            ->select('u AS user,s.value AS itemsDonated')
            ->leftJoin('App:UserStats', 's', Expr\Join::WITH, 's.user = u.id')
            ->andWhere('s.stat = :statName')
            ->addOrderBy('s.value', 'DESC')
            ->addOrderBy('s.lastTime', 'ASC')
            ->setParameter('statName', 'Items Donated to Museum')
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

        return $responseService->success($results, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MUSEUM ]);
    }

    /**
     * @Route("/donate", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getItems(
        ResponseService $responseService, Request $request, InventoryRepository $inventoryRepository,
        MuseumItemRepository $museumItemRepository, EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedMuseum() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $inventoryIds = $request->request->get('inventory');

        if(is_array($inventoryIds) && count($inventoryIds) > 20)
            throw new UnprocessableEntityHttpException('You may only donate up to 20 items at a time.');

        /** @var Inventory[] $inventory */
        $inventory = $inventoryRepository->findBy([ 'id' => $inventoryIds ]);

        if(count($inventory) === 0)
            throw new UnprocessableEntityHttpException('No items were selected.');

        for($i = count($inventory) - 1; $i >= 0; $i--)
        {
            if($inventory[$i]->getOwner()->getId() !== $user->getId())
            {
                unset($inventory[$i]);
                continue;
            }

            $existingItem = $museumItemRepository->findOneBy([
                'user' => $user,
                'item' => $inventory[$i]->getItem()
            ]);

            if($existingItem)
            {
                unset($inventory[$i]);
                continue;
            }
        }

        if(count($inventory) === 0)
            throw new UnprocessableEntityHttpException('Some of the selected items could not be donated? That\'s weird. Try reloading and trying again.');

        foreach($inventory as $i)
        {
            $museumItem = (new MuseumItem())
                ->setUser($user)
                ->setItem($i->getItem())
                ->setCreatedBy($i->getCreatedBy())
                ->setComments($i->getComments())
            ;

            $em->persist($museumItem);
            $em->remove($i);
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, count($inventory));

        $em->flush();

        return $responseService->success();
    }
}
