<?php
namespace App\Controller;

use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("")
 */
class IndexController extends PsyPetsController
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
                'Shirley Farrow',
                'pericarditis',
                'All my friends in college',
            ]
        ]);
    }

    /**
     * @Route("/myIp")
     */
    public function clientIP(ResponseService $responseService, Request $request)
    {
        return $responseService->success($request->getClientIp());
    }
}
