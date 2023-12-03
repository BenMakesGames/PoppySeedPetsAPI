<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/dirtyLump")
 */
class DirtyLumpController extends AbstractController
{
    #[Route("/{lump}/clean", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function clean(
        Inventory $lump, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $lump, 'dirtyLump/#/clean');

        $location = $lump->getLocation();
        $lockedToOwner = $lump->getLockedToOwner();

        $item = $rng->rngNextFromArray([
            'Secret Seashell',
            'Striped Microcline',
            'Blackonite',
            $rng->rngNextFromArray([ 'Meteorite', 'Species Transmigration Serum' ]),
            'Century Egg',
            'Digital Camera',
            'Beta Bug',
            'White Bow',
            'Fish Bones',
            'Worms',
            $rng->rngNextFromArray([ 'Potion of Stealth', 'Werebane' ]),
            'Propeller Beanie',
            'Rusted, Busted Mechanism',
            $rng->rngNextFromArray([ 'Tile: Hidden Alcove', 'Tile: Run-down Orchard' ]),
            'Tower Chest',
            'Major Scroll of Riches',
            'Sand-covered... Something',
            'Monster Box',
            'Lunchbox Paint',
            $rng->rngNextFromArray([ 'Flowerbomb', 'Giant Turkey Leg' ]),
        ]);

        $itemObject = ItemRepository::findOneByName($em, $item);

        $userStatsRepository->incrementStat($user, 'Cleaned a ' . $lump->getItem()->getName());

        $inventoryService->receiveItem($itemObject, $user, $lump->getCreatedBy(), $user->getName() . ' found this covered in dirt.', $location, $lockedToOwner);
        $message = 'You clean off the object, which reveals itself to be ' . $itemObject->getNameWithArticle() . '!';

        $em->remove($lump);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
