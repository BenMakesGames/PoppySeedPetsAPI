<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/item/bee")]
class BeeController extends AbstractController
{
    #[Route("/{inventory}/giveToBeehive", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function giveToBeehive(Inventory $inventory, EntityManagerInterface $em, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'bee/#/giveToBeehive');

        if(!$user->getBeehive())
            return $responseService->itemActionSuccess("On second thought, it occurs to you that you don't know of any Beehive to put this Bee in...");

        $user->getBeehive()
            ->addWorkers(1)
            ->setFlowerPower(36)
            ->setInteractionPower()
        ;

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess('You introduce the Bee to Queen ' . $user->getBeehive()->getQueenName() . ', who thanks you for your honor and loyalty. The colony redoubles their efforts, and hey: with 1 more worker than before! (Every bee counts!)', [ 'itemDeleted' => true ]);
    }
}