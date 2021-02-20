<?php
namespace App\Controller\Item;


use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/butterknife")
 */
class ButterknifeController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/mold", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function moldButterknife(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'butterknife/#/mold');

        $butter = $itemRepository->findOneByName('Butter');

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($butter)
            ->setModifiedOn()
        ;

        $em->flush();

        $responseService->addFlashMessage('There we go. Back to normal.');
        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess(null);
    }
}
