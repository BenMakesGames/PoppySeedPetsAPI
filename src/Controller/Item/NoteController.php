<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/note")
 */
class NoteController extends PsyPetsItemController
{
    /**
     * @Route("/{inventory}/erase", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function eraseNote(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'note/#/erase');

        $paper = $itemRepository->findOneByName('Paper');

        $inventory
            ->setItem($paper)
            ->addComment($user->getName() . ' erased the message that had been written on this Paper.');
        ;

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/welcome/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readWelcomeNote(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'note/welcome/#/read');

        return $responseService->itemActionSuccess('# Welcome!

Thanks for adopting a pet! So many of these creatures have been appearing from the portal, we\'re having trouble taking care of them all!

Here\'s some basic notes on how to take care of your new pet:

1. Visit the Plaza every week to get a free box of food stuffs!
1. Roughly every hour, your pet will go out and do stuff, all on its own! These little guys like to fish, hunt, gather, and even make their own tools!
1. Touch your pet to interact with it, and see a log of what it\'s been up to.
1. Have fun!
');
    }

    /**
     * @Route("/cobblers/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readCobblerRecipes(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'note/cobblers/#/read');

        return $responseService->itemActionSuccess('* flour
* milk
* butter
* sugar
* b powder
* berries');
    }
}