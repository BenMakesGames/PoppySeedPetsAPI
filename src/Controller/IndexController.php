<?php
namespace App\Controller;

use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("")
 */
class IndexController extends PoppySeedPetsController
{
    /**
     * @Route("/about")
     */
    public function about(ResponseService $responseService)
    {
        return $responseService->success([
            'design' => [ 'Ben Hendel-Doying' ],
            'programming' => [ 'Ben Hendel-Doying' ],
            'art' => [ 'Aileen MacKay', 'Ben Hendel-Doying' ],
            'thanks' => [
                'Hector Lee',
                'Katie Stanonik',
                'Mothnox',
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
