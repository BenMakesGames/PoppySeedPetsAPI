<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/item")]
class RijndaelController extends AbstractController
{
    #[Route("/rijndael/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function search(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'rijndael');

        $searchForId = $request->request->getInt('itemId');

        if(!$searchForId)
            throw new PSPFormValidationException('An item to search for must be selected!');

        $itemToFind = ItemRepository::findOneById($em, $searchForId);

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

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Elvish Magnifying Glass'));

        $em->flush();

        return $responseService->success($results);
    }

}
