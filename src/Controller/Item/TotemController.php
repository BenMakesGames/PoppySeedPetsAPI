<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\TotemPole;
use App\Entity\TotemPoleTotem;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/totem")
 */
class TotemController extends PoppySeedPetsItemController
{
    private const TOTEM_HEIGHTS = [
    ];

    /**
     * @Route("/{inventory}/add", methods={"POST"})
     */
    public function addToPole(Inventory $inventory, EntityManagerInterface $em, ResponseService $responseService)
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'totem/#/add');

        $message = null;

        if(!$user->getUnlockedTotemPoleGarden())
        {
            $user->setUnlockedTotemPoleGarden();

            $totemPole = (new TotemPole())
                ->setOwner($user)
            ;

            $em->persist($totemPole);

            $message = 'One small step... (the Totem Pole has been added to the menu!)';
        }

        $appearance = $inventory->getItem()->getImage();
        $totemPole = $user->getTotemPole();
        $oldHeight = $totemPole->getHeightInCentimeters();

        $totemPole
            ->increaseHeight()
            ->increaseHeightInCentimeters(self::TOTEM_HEIGHTS[$inventory->getItem()->getName()])
        ;

        $totem = (new TotemPoleTotem())
            ->setOwner($user)
            ->setAppearance($appearance)
            ->setOrdinal($totemPole->getHeight())
        ;

        $newHeight = $totemPole->getHeightInCentimeters();

        if($message === null)
        {
            if($oldHeight < 1000 && $newHeight >= 1000)
            {
                $message = 'Your totem pole is now ' . round($newHeight / 100, 1) . 'm tall! Visit the Totem Garden to collect a reward!';
                $totemPole->setReward10m(true);
            }
            else if($oldHeight < 5000 && $newHeight >= 5000)
            {
                $message = 'Your totem pole is now ' . round($newHeight / 100, 1) . 'm tall! Visit the Totem Garden to collect a reward!';
                $totemPole->setReward50m(true);
            }
            else if($oldHeight <= 900000 && $newHeight > 900000)
            {
                // > 9000m
                $message = 'Your totem pole... IT\'S OVER 9000m! Visit the Totem Garden to collect a reward!';
                $totemPole->setReward9000m(true);
            }
            else if(floor($oldHeight / 100000) < floor($newHeight / 10000) && $newHeight !== 900000)
            {
                $message = 'Your totem pole is now ' . round($newHeight / 100, 1) . 'm tall! Visit the Totem Garden to collect a reward!';
                $totemPole->setReward100m(true);
            }
            else
                $message = 'Your totem pole is now ' . round($newHeight / 100, 1) . 'm tall. One, tiny step closer...';
        }

        $em->persist($totem);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}