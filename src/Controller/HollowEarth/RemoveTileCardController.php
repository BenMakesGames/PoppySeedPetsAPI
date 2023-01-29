<?php
namespace App\Controller\HollowEarth;

use App\Entity\HollowEarthPlayerTile;
use App\Repository\HollowEarthPlayerTileRepository;
use App\Repository\HollowEarthTileRepository;
use App\Repository\InventoryRepository;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class RemoveTileCardController extends AbstractController
{
    /**
     * @Route("/removeTileCard", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function removeTileCard(
        Request $request, HollowEarthPlayerTileRepository $hollowEarthPlayerTileRepository,
        ResponseService $responseService, EntityManagerInterface $em, HollowEarthTileRepository $hollowEarthTileRepository,
        InventoryRepository $inventoryRepository, HollowEarthService $hollowEarthService
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new AccessDeniedHttpException();

        if($player->getCurrentAction())
            throw new UnprocessableEntityHttpException('You can\'t change the map while you\'re moving!');

        $tileId = $request->request->getInt('tile', 0);

        $tile = $hollowEarthTileRepository->find($tileId);

        if(!$tile)
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth does not exist?!?! (Maybe reload and try again...)');

        if($tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed')
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth cannot be changed!');

        $existingPlayerTile = $hollowEarthPlayerTileRepository->findOneBy([
            'player' => $user,
            'tile' => $tile,
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setCard(null);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setCard(null)
            ;

            $em->persist($playerTile);
        }

        $em->flush();

        return $responseService->success();
    }
}
