<?php
namespace App\Controller\HollowEarth;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class MyTilesController extends AbstractController
{
    /**
     * @Route("/myTiles", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyTiles(
        InventoryRepository $inventoryRepository, ResponseService $responseService, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        $types = $request->query->get('types', []);

        if(!is_array($types) || count($types) === 0)
            throw new UnprocessableEntityHttpException('The types of tiles is missing.');

        $tiles = $inventoryRepository->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.hollowEarthTileCard', 'tileCard')
            ->leftJoin('tileCard.type', 'type')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.hollowEarthTileCard IS NOT NULL')
            ->andWhere('type.name IN (:allowedTypes)')
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->setParameter('allowedTypes', $types)
            ->getQuery()
            ->execute()
        ;

        return $responseService->success($tiles, [ SerializationGroupEnum::MY_HOLLOW_EARTH_TILES ]);
    }
}
