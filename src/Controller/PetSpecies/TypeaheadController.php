<?php
declare(strict_types=1);

namespace App\Controller\PetSpecies;

use App\Attributes\DoesNotRequireHouseHours;
use App\Service\ResponseService;
use App\Service\Typeahead\PetSpeciesTypeaheadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/petSpecies")]
class TypeaheadController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/typeahead", methods: ["GET"])]
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, PetSpeciesTypeaheadService $petSpeciesTypeaheadService
    )
    {
        $suggestions = $petSpeciesTypeaheadService->search('name', $request->query->get('search', ''));

        return $responseService->success($suggestions, [ "typeahead" ]);
    }

}