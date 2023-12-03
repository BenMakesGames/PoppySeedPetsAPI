<?php
namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/note")
 */
class WelcomeNoteController extends AbstractController
{
    #[Route("/welcome/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readWelcomeNote(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/welcome/#/read');

        return $responseService->itemActionSuccess('# Welcome!

Thanks for adopting a pet! So many of these creatures have been appearing from the portal, we\'re having trouble taking care of them all!

Here\'s some basic notes on how to take care of your new pet:

1. Visit the Plaza every week to get a free box of food stuffs!
1. Roughly every hour, your pet will go out and do stuff, all on its own! These little guys like to fish, hunt, gather, and even make their own tools!
1. Touch your pet to interact with it, and see a log of what it\'s been up to.
1. Have fun!
');
    }
}
