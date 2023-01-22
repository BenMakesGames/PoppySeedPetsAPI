<?php
namespace App\Controller\HollowEarth;

use App\Controller\PoppySeedPetsController;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class MyTilesController extends PoppySeedPetsController
{
    /**
     * @Route("/myTiles", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyTiles(
        InventoryRepository $inventoryRepository, ResponseService $responseService, Request $request
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        $types = $request->query->get('types', []);

        if(!is_array($types) || count($types) === 0)
            throw new UnprocessableEntityHttpException('The types of tiles is missing.');

        $tiles = $inventoryRepository->findHollowEarthTiles($user, $types);

        return $responseService->success($tiles, [ SerializationGroupEnum::MY_HOLLOW_EARTH_TILES ]);
    }
}
