<?php

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/pizzaBox")
 */
class PizzaBoxController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function openPizzaBox(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        UserStatsService $userStatsRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pizzaBox/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $newInventory = [];

        $description = $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.';
        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $possibleSlices = $em->getRepository(Item::class)->createQueryBuilder('i')
            ->leftJoin('i.food', 'f')
            ->join('i.itemGroups', 'g')
            ->andWhere('f IS NOT NULL')
            ->andWhere('g.name = :za')
            ->setParameter('za', 'Za')
            ->getQuery()
            ->execute();

        $numSlices = $rng->rngNextFromArray([
            3, 3, 3, 3, 3, 4, 4, 4, 5, 6, // averages 3.8 (slightly less than 4, to avoid an infinite pizza box engine)
        ]);

        for($i = 0; $i < $numSlices; $i++)
        {
            $newInventory[] = $inventoryService->receiveItem($rng->rngNextFromArray($possibleSlices), $user, $user, $description, $location, $locked)
                ->setSpice($inventory->getSpice())
            ;
        }

        return BoxHelpers::countRemoveFlushAndRespond('You open the box, finding', $userStatsRepository, $user, $inventory, $newInventory, $responseService, $em);
    }

}