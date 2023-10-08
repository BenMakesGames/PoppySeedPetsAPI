<?php
namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/note")
 */
class NoteController extends AbstractController
{
    /**
     * @Route("/{inventory}/erase", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function eraseNote(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'note/#/erase');

        $paper = ItemRepository::findOneByName($em, 'Paper');

        $inventory
            ->changeItem($paper)
            ->addComment($user->getName() . ' erased the message that had been written on this Paper.')
        ;

        $responseService->setReloadInventory();

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
