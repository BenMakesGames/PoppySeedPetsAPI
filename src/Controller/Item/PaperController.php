<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/paper")
 */
class PaperController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/unfold", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unfoldPaperThing(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'paper/#/unfold');

        $paper = $itemRepository->findOneByName('Paper');

        $newComment = $user->getName() . ' unfolded ' . $inventory->getItem()->getNameWithArticle() . ' into this simple piece of Paper.';

        $inventory
            ->changeItem($paper)
            ->addComment($newComment)
        ;

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
