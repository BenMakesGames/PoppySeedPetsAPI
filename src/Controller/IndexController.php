<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route("")]
class IndexController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     */
    #[Route("/about")]
    public function about(ResponseService $responseService)
    {
        return $responseService->success([
            'design' => [ 'Ben Hendel-Doying' ],
            'programming' => [ 'Ben Hendel-Doying' ],
            'art' => [
                'Aileen MacKay',
                'Ben Hendel-Doying',
                'Sabrina Silli',
                'Hae-Rhee',
                'TBNRskye',
                'Moopyloots',
                'Mothnox',
                'Vermidia',
            ],
            'thanks' => [
                'Hector Lee',
                'Katie Stanonik',
                'Mothnox',
                'Verdale',
                'pericarditis',
                'Shirley Farrow',
                'All my friends in college',
                'Tomi',
                'Vicious Ruff',
                'Onyx',
            ],
            'inspirations' => [
                'PsyPets',
                'The Sims',
                'Dwarf Fortress',
                'Dofus',
                'Kingdom of Loathing',
            ],
            'madeWith' => [
                'Symfony', 'PHPStorm', 'existential nihilism', 'absurdism', 'humanism', 'candy'
            ]
        ]);
    }
}
