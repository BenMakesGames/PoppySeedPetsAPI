<?php
namespace App\Controller\Item;


use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/butterknife")
 */
class ButterknifeController extends AbstractController
{
    /**
     * @Route("/{inventory}/mold", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function moldButterknife(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'butterknife/#/mold');

        $butter = ItemRepository::findOneByName($em, 'Butter');

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
