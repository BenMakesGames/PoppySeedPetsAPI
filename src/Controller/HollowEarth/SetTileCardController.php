<?php
namespace App\Controller\HollowEarth;

use App\Entity\HollowEarthPlayerTile;
use App\Entity\HollowEarthTile;
use App\Entity\HollowEarthTileType;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/hollowEarth")]
class SetTileCardController extends AbstractController
{
    #[Route("/setTileCard", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setTileCard(
        Request $request,
        ResponseService $responseService, EntityManagerInterface $em, HollowEarthService $hollowEarthService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new PSPInvalidOperationException('You gotta\' visit the Hollow Earth page at least once before taking this kind of action.');

        if($player->getCurrentAction())
            throw new PSPInvalidOperationException('You can\'t change the map while you\'re moving!');

        $tileId = $request->request->getInt('tile', 0);
        $inventoryId = $request->request->getInt('item', 0);

        $tile = $em->getRepository(HollowEarthTile::class)->find($tileId);

        if(!$tile)
            throw new PSPNotFoundException('That space in the Hollow Earth does not exist?!?! (Maybe reload and try again...)');

        if($tile->getCard() && $tile->getCard()->getType()->getName() === 'Fixed')
            throw new PSPInvalidOperationException('That space in the Hollow Earth cannot be changed!');

        $inventory = $em->getRepository(Inventory::class)->findOneBy([
            'id' => $inventoryId,
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$inventory)
            throw new PSPNotFoundException('That item couldn\'t be found! (Reload and try again.)');

        $card = $inventory->getItem()->getHollowEarthTileCard();

        if(!$card)
            throw new PSPFormValidationException('That item isn\'t a Hollow Earth Tile! (Weird! Reload and try again...)');

        $canUseTile = ArrayFunctions::any($tile->getTypes(), fn(HollowEarthTileType $tt) => $tt->getId() === $card->getType()->getId());

        if(!$canUseTile)
            throw new PSPFormValidationException('You can\'t use that Tile on this space! (The types don\'t match!)');

        $cardIdsOnMap = $hollowEarthService->getAllCardIdsOnMap($user);

        if(array_search($card->getId(), $cardIdsOnMap))
            throw new PSPInvalidOperationException('You already have that Tile on the map! (Each Tile can only appear once!)');

        $existingPlayerTile = $em->getRepository(HollowEarthPlayerTile::class)->findOneBy([
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
