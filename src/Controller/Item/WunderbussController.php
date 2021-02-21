<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\MuseumItem;
use App\Enum\LocationEnum;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item")
 */
class WunderbussController extends PoppySeedPetsItemController
{
    /**
     * @Route("/wunderbuss/{inventory}/usedWish", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function usedWish(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository
    )
    {
        $this->validateInventory($inventory, 'wunderbuss');

        $user = $this->getUser();

        $usedAWunderbuss = $userQuestRepository->findOrCreate($user, 'Used a Wunderbuss', false);

        return $responseService->success($usedAWunderbuss->getValue());
    }

    /**
     * @Route("/wunderbuss/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function search(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        ItemRepository $itemRepository, MuseumItemRepository $museumItemRepository, UserQuestRepository $userQuestRepository
    )
    {
        $this->validateInventory($inventory, 'wunderbuss');

        $user = $this->getUser();

        $usedAWunderbuss = $userQuestRepository->findOrCreate($user, 'Used a Wunderbuss', false);

        if($usedAWunderbuss->getValue())
            throw new UnprocessableEntityHttpException('You\'ve already wished for something from the Wunderboss. (You only get one wish, remember?)');

        $searchForId = $request->request->get('itemId');

        if(!$searchForId)
            throw new UnprocessableEntityHttpException('An item to search for must be selected!');

        $itemToFind = $itemRepository->find($searchForId);

        if(!$itemToFind)
            throw new NotFoundHttpException('The item you selected could not be found... that\'s really weird. Reload and try again??');

        $donatedItem = $museumItemRepository->findOneBy([
            'item' => $itemToFind,
            'user' => $user,
        ]);

        if($donatedItem)
            throw new UnprocessableEntityHttpException('You\'ve already donated ' . $itemToFind->getNameWithArticle() . '.');

        // 1. donate the item
        $newDonatedItem = (new MuseumItem())
            ->setUser($user)
            ->setItem($itemToFind)
            ->setComments([ 'This item was created by wishing for it from Wunderboss!' ])
        ;

        $em->persist($newDonatedItem);

        // 2. count the wish as granted
        $usedAWunderbuss->setValue(true);

        // 3. it is done
        $em->flush();

        $responseService->setReloadInventory(true);
        $responseService->addFlashMessage('IT IS DONE! Olfert\'s spirit thanks you, as do I!');

        return $responseService->success();
    }

}
