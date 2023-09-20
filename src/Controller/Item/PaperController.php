<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/paper")
 */
class PaperController extends AbstractController
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
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'paper/#/unfold');

        $paper = $itemRepository->deprecatedFindOneByName('Paper');

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
