<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/paper")]
class PaperController extends AbstractController
{
    #[Route("/{inventory}/unfold", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function unfoldPaperThing(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'paper/#/unfold');

        $paper = ItemRepository::findOneByName($em, 'Paper');

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
