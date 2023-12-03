<?php
namespace App\Controller\HollowEarth;

use App\Entity\HollowEarthPlayerTile;
use App\Entity\HollowEarthTile;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/hollowEarth")]
class RemoveTileCardController extends AbstractController
{
    #[Route("/removeTileCard", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function removeTileCard(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new PSPNotUnlockedException('Portal');

        if($player->getCurrentAction())
            throw new PSPInvalidOperationException('You can\'t change the map while you\'re moving!');

        $tileId = $request->request->getInt('tile', 0);

        $tile = $em->getRepository(HollowEarthTile::class)->find($tileId);

        if(!$tile)
            throw new PSPNotFoundException('That space in the Hollow Earth does not exist?!?! (Maybe reload and try again...)');

        if($tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed')
            throw new PSPInvalidOperationException('That space in the Hollow Earth cannot be changed!');

        $existingPlayerTile = $em->getRepository(HollowEarthPlayerTile::class)->findOneBy([
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
