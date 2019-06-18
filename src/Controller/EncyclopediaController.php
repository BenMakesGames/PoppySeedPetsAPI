<?php
namespace App\Controller;

use App\Entity\Item;
use App\Enum\SerializationGroup;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\RecipeRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/encyclopedia")
 */
class EncyclopediaController extends PsyPetsController
{
    /**
     * @Route("/item/{itemName}", methods={"GET"})
     */
    public function getMyInventory(string $itemName, ItemRepository $itemRepository, ResponseService $responseService)
    {
        $item = $itemRepository->findOneBy([ 'name' => $itemName ]);

        if(!$item)
            throw new NotFoundHttpException();

        return $responseService->success($item, null, SerializationGroup::ENCYCLOPEDIA);
    }

}