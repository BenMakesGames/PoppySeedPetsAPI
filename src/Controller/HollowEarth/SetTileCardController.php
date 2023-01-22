<?php
namespace App\Controller\HollowEarth;

use App\Controller\PoppySeedPetsController;
use App\Entity\HollowEarthPlayerTile;
use App\Entity\HollowEarthTileType;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Repository\HollowEarthPlayerTileRepository;
use App\Repository\HollowEarthTileRepository;
use App\Repository\InventoryRepository;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class SetTileCardController extends PoppySeedPetsController
{
    /**
     * @Route("/setTileCard", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setTileCard(
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
        $inventoryId = $request->request->getInt('item', 0);

        $tile = $hollowEarthTileRepository->find($tileId);

        if(!$tile)
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth does not exist?!?! (Maybe reload and try again...)');

        if($tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed')
            throw new UnprocessableEntityHttpException('That space in the Hollow Earth cannot be changed!');

        $inventory = $inventoryRepository->findOneBy([
            'id' => $inventoryId,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$inventory)
            throw new UnprocessableEntityHttpException('That item couldn\'t be found! (Reload and try again.)');

        $card = $inventory->getItem()->getHollowEarthTileCard();

        if(!$card)
            throw new UnprocessableEntityHttpException('That item isn\'t a Hollow Earth Tile! (Weird! Reload and try again...)');

        $canUseTile = ArrayFunctions::any($tile->getTypes(), fn(HollowEarthTileType $tt) => $tt->getId() === $card->getType()->getId());

        if(!$canUseTile)
            throw new UnprocessableEntityHttpException('You can\'t use that Tile on this space! (The types don\'t match!)');

        $cardIdsOnMap = $hollowEarthService->getAllCardIdsOnMap($user);

        if(array_search($card->getId(), $cardIdsOnMap))
            throw new UnprocessableEntityHttpException('You already have that Tile on the map! (Each Tile can only appear once!)');

        $existingPlayerTile = $hollowEarthPlayerTileRepository->findOneBy([
            'player' => $user,
            'tile' => $tile,
        ]);

        if($existingPlayerTile)
        {
            $existingPlayerTile->setCard($card);
        }
        else
        {
            $playerTile = (new HollowEarthPlayerTile())
                ->setPlayer($user)
                ->setTile($tile)
                ->setCard($card)
            ;

            $em->persist($playerTile);
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->success();
    }
}
