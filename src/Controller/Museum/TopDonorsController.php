<?php
namespace App\Controller\Museum;

use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Model\FilterResults;
use App\Repository\UserRepository;
use App\Service\ResponseService;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/museum")
 */
class TopDonorsController extends AbstractController
{
    /**
     * @Route("/topDonors", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getTopDonors(
        Request $request, ResponseService $responseService, UserRepository $userRepository, NormalizerInterface $normalizer
    )
    {
        if(!$this->getUser()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $qb = $userRepository->createQueryBuilder('u')
            ->select('u AS user,s.value AS itemsDonated')
            ->leftJoin('App:UserStats', 's', Expr\Join::WITH, 's.user = u.id')
            ->andWhere('s.stat = :statName')
            ->addOrderBy('s.value', 'DESC')
            ->addOrderBy('s.lastTime', 'ASC')
            ->setParameter('statName', UserStatEnum::ITEMS_DONATED_TO_MUSEUM)
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
}
