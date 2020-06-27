<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Repository\ItemRepository;
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
class RijndaelController extends PoppySeedPetsItemController
{
    /**
     * @Route("/rijndael/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function search(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'rijndael');

        $searchForId = $request->request->get('itemId');

        if(!$searchForId)
            throw new UnprocessableEntityHttpException('An item to search for must be selected!');

        $itemToFind = $itemRepository->find($searchForId);

        if(!$itemToFind)
            throw new NotFoundHttpException('The item you selected could not be found... that\'s really weird. Reload and try again??');

        $results = $em->createQueryBuilder()
            ->select('u.name', 'u.id', 'count(i.id) AS quantity')
            ->from('App\\Entity\\Inventory', 'i')
            ->join('i.owner', 'u')
            ->andWhere('i.item=:item')
            ->addGroupBy('i.owner')
            ->addOrderBy('quantity', 'desc')
            ->setMaxResults(20)
            ->setParameter('item', $itemToFind)
            ->getQuery()
            ->getArrayResult()
        ;

        $inventory->changeItem($itemRepository->findOneByName('Elvish Magnifying Glass'));

        $em->flush();

        return $responseService->success($results);
    }

}
