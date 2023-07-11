<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Enum\SerializationGroupEnum;
use App\Repository\ItemRepository;
use App\Repository\PetSpeciesRepository;
use App\Service\Filter\ItemFilterService;
use App\Service\Filter\MeritFilterService;
use App\Service\Filter\PetSpeciesFilterService;
use App\Service\ResponseService;
use App\Service\Typeahead\ItemTypeaheadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/encyclopedia")
 */
class EncyclopediaController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/typeahead/item", methods={"GET"})
     */
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, ItemTypeaheadService $itemTypeaheadService
    )
    {
        try
        {
            $suggestions = $itemTypeaheadService->search('name', $request->query->get('search', ''), 5);

            return $responseService->success($suggestions, [ SerializationGroupEnum::ITEM_TYPEAHEAD ]);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/item", methods={"GET"})
     */
    public function itemSearch(Request $request, ItemFilterService $itemFilterService, ResponseService $responseService)
    {
        $itemFilterService->setUser($this->getUser());

        return $responseService->success(
            $itemFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/item/{itemName}", methods={"GET"})
     */
    public function getItemByName(string $itemName, ItemRepository $itemRepository, ResponseService $responseService)
    {
        try
        {
            $item = $itemRepository->findOneByName($itemName);

            return $responseService->success($item, [ SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new NotFoundHttpException('There is no such item.');
        }
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/species", methods={"GET"})
     */
    public function speciesSearch(Request $request, PetSpeciesFilterService $petSpeciesFilterService, ResponseService $responseService)
    {
        $petSpeciesFilterService->setUser($this->getUser());

        return $responseService->success(
            $petSpeciesFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ENCYCLOPEDIA ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/species/{speciesName}", methods={"GET"})
     */
    public function getSpeciesByName(string $speciesName, PetSpeciesRepository $petSpeciesRepository, ResponseService $responseService)
    {
        $species = $petSpeciesRepository->findOneBy([ 'name' => $speciesName ]);

        if(!$species)
            throw new NotFoundHttpException('There is no such species.');

        return $responseService->success($species, [ SerializationGroupEnum::PET_ENCYCLOPEDIA ]);
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/merit", methods={"GET"})
     */
    public function getMerits(
        ResponseService $responseService, MeritFilterService $meritFilterService, Request $request
    )
    {
        return $responseService->success(
            $meritFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MERIT_ENCYCLOPEDIA ]
        );
    }

}
