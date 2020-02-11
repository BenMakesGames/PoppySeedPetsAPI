<?php
namespace App\Controller\Item\Blueprint;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use App\Service\BeehiveService;
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
    public function buildBasement(
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

    /**
     * @Route("/beehive/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBeehive(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, BeehiveService $beehiveService
    )
    {
        $this->validateInventory($inventory, 'blueprint/beehive/#/read');

        $user = $this->getUser();

        if($user->getUnlockedBeehive())
        {
            return $responseService->itemActionSuccess('You\'ve already got a Beehive!');
        }
        else if(!$inventoryRepository->userHasAnyOneOf($user, [ '"Rustic" Magnifying Glass', 'Elvish Magnifying Glass', 'Rijndael' ]))
        {
            return $responseService->itemActionSuccess('Goodness! It\'s so small! You\'ll need a magnifying glass of some kind...');
        }
        else
        {
            $em->remove($inventory);

            $user->setUnlockedBeehive();

            $beehiveService->createBeehive($user);

            $em->flush();

            return $responseService->itemActionSuccess(
                'The blueprint is _super_ tiny, but with the help of a magnifying glass, you\'re able to make it all out.' . "\n\n" . 'You now have a Beehive!',
                [ 'reloadInventory' => true, 'itemDeleted' => true ]
            );
        }

    }
}
