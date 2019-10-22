<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/greenhouseDeed")
 */
class GreenhouseDeedController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/claim", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claim(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'greenhouseDeed/#/claim');

        $user = $this->getUser();

        if($user->getUnlockedGreenhouse())
        {
            return $responseService->itemActionSuccess('You\'ve already claimed a plot in the Greenhouse.');
        }
        else
        {
            $user->setUnlockedGreenhouse();
            $em->remove($inventory);

            $em->flush();

            return $responseService->itemActionSuccess('You now own a plot in the Greenhouse!', [ 'reloadInventory' => true, 'itemDeleted' => true ]);
        }
    }
}