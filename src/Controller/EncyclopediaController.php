<?php
namespace App\Controller;

use App\Entity\Item;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\RecipeRepository;
use App\Service\Filter\ItemFilterService;
use App\Service\Filter\PetSpeciesFilterService;
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
class EncyclopediaController extends PoppySeedPetsController
{
    /**
     * @Route("/item", methods={"GET"})
     */
    public function itemSearch(Request $request, ItemFilterService $itemFilterService, ResponseService $responseService)
    {
        return $responseService->success(
            $itemFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]
        );
    }

    /**
     * @Route("/item/{itemName}", methods={"GET"})
     */
    public function getItemByName(string $itemName, ItemRepository $itemRepository, ResponseService $responseService)
    {
        $item = $itemRepository->findOneBy([ 'name' => $itemName ]);

        if(!$item)
            throw new NotFoundHttpException();

        return $responseService->success($item, SerializationGroupEnum::ITEM_ENCYCLOPEDIA);
    }

    /**
     * @Route("/species", methods={"GET"})
     */
    public function speciesSearch(Request $request, PetSpeciesFilterService $petSpeciesFilterService, ResponseService $responseService)
    {
        return $responseService->success(
            $petSpeciesFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ENCYCLOPEDIA ]
        );
    }

    /**
     * @Route("/species/{speciesName}", methods={"GET"})
     */
    public function getSpeciesByName(string $speciesName, PetSpeciesRepository $petSpeciesRepository, ResponseService $responseService)
    {
        $species = $petSpeciesRepository->findOneBy([ 'name' => $speciesName ]);

        if(!$species)
            throw new NotFoundHttpException();

        return $responseService->success($species, SerializationGroupEnum::PET_ENCYCLOPEDIA);
    }

}