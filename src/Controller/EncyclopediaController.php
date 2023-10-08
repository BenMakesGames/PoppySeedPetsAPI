<?php
namespace App\Controller;

use App\Entity\PetSpecies;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ItemRepository;
use App\Service\Filter\ItemFilterService;
use App\Service\Filter\MeritFilterService;
use App\Service\Filter\PetSpeciesFilterService;
use App\Service\ResponseService;
use App\Service\Typeahead\ItemTypeaheadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

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
        $suggestions = $itemTypeaheadService->search('name', $request->query->get('search', ''), 5);

        return $responseService->success($suggestions, [ SerializationGroupEnum::ITEM_TYPEAHEAD ]);
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
    public function getItemByName(string $itemName, EntityManagerInterface $em, ResponseService $responseService)
    {
        try
        {
            $item = ItemRepository::findOneByName($em, $itemName);

            return $responseService->success($item, [ SerializationGroupEnum::ITEM_ENCYCLOPEDIA ]);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new PSPNotFoundException('There is no such item.');
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
    public function getSpeciesByName(string $speciesName, EntityManagerInterface $em, ResponseService $responseService)
    {
        $species = $em->getRepository(PetSpecies::class)->findOneBy([ 'name' => $speciesName ]);

        if(!$species)
            throw new PSPNotFoundException('There is no such species.');

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
