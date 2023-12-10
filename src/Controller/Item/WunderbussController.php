<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\MuseumItem;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Service\MuseumService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class WunderbussController extends AbstractController
{
    #[Route("/wunderbuss/{inventory}/usedWish", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function usedWish(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wunderbuss');

        $usedAWunderbuss = UserQuestRepository::findOrCreate($em, $user, 'Used a Wunderbuss', false);

        return $responseService->success($usedAWunderbuss->getValue());
    }

    #[Route("/wunderbuss/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function search(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        MuseumService $museumService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wunderbuss');

        $usedAWunderbuss = UserQuestRepository::findOrCreate($em, $user, 'Used a Wunderbuss', false);

        if($usedAWunderbuss->getValue())
            throw new PSPInvalidOperationException('You\'ve already wished for something from the Wunderbuss. (You only get one wish, unfortunately...)');

        $searchForId = $request->request->get('itemId');

        if(!$searchForId)
            throw new PSPFormValidationException('An item to search for must be selected!');

        $itemToFind = ItemRepository::findOneById($em, $searchForId);

        $donatedItem = $em->getRepository(MuseumItem::class)->findOneBy([
            'item' => $itemToFind,
            'user' => $user,
        ]);

        if($donatedItem)
            throw new PSPInvalidOperationException('You\'ve already donated ' . $itemToFind->getNameWithArticle() . '.');

        // 1. donate the item
        $museumService->forceDonateItem($user, $itemToFind, 'This item was created by wishing for it from Wunderboss!');

        // 2. count the wish as granted
        $usedAWunderbuss->setValue(true);

        // 3. it is done
        $em->flush();

        $responseService->setReloadInventory(true);
        $responseService->addFlashMessage('IT IS DONE! Olfert\'s spirit thanks you, as do I!');

        return $responseService->success();
    }

}
