<?php
namespace App\Controller\PetSpecies;

use App\Service\ResponseService;
use App\Service\Typeahead\PetSpeciesTypeaheadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/petSpecies")
 */
class TypeaheadController extends AbstractController
{

    /**
     * @Route("/typeahead", methods={"GET"})
     * @DoesNotRequireHouseHours()
     */
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, PetSpeciesTypeaheadService $petSpeciesTypeaheadService
    )
    {
        $suggestions = $petSpeciesTypeaheadService->search('name', $request->query->get('search', ''));

        return $responseService->success($suggestions, [ "typeahead" ]);
    }

}