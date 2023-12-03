<?php
namespace App\Controller\HollowEarth;

use App\Entity\HollowEarthPlayerTile;
use App\Entity\User;
use App\Exceptions\PSPInvalidOperationException;
use App\Repository\HollowEarthPlayerTileRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/hollowEarth")]
class ChangeTileGoodsController extends AbstractController
{
    #[Route("/changeTileGoods", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changeTileGoods(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        $selectedGoods = $request->request->getAlpha('goods');

        $tile = $player->getCurrentTile();

        if(!$tile || !$tile->getGoods() || count($tile->getGoods()) === 0)
            throw new PSPInvalidOperationException('You are not on a tile that produces goods.');

        if($player->getCurrentAction() || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('You can\'t change goods while you\'re moving!');

        if(!in_array($selectedGoods, $tile->getGoods()))
            throw new PSPInvalidOperationException('This tile is not capable of producing that type of good.');

        $existingPlayerTile = $em->getRepository(HollowEarthPlayerTile::class)->findOneBy([
            'player' => $user,
            'tile' => $tile->getId(),
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setGoods($selectedGoods);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setGoods($selectedGoods)
                ->setCard($tile->getCard())
            ;

            $em->persist($playerTile);
        }

        $em->flush();

        return $responseService->success();
    }
}
