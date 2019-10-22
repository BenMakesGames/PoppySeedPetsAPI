<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/blueprint")
 */
class BlueprintController extends PoppySeedPetsItemController
{
    /**
     * @Route("/basement/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claim(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'blueprint/basement/#/read');

        $user = $this->getUser();

        if($user->getUnlockedBasement())
        {
            return $responseService->itemActionSuccess('You\'ve already got a Basement!');
        }
        else
        {
            $user->setUnlockedBasement();
            $em->remove($inventory);

            $em->flush();

            return $responseService->itemActionSuccess(
                'You now have a Basement! (Somehow?? (Shh, just accept it...))',
                [ 'reloadInventory' => true, 'itemDeleted' => true ]
            );
        }
    }
}