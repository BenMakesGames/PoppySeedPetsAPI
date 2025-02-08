<?php
declare(strict_types=1);

namespace App\Controller\Article;

use App\Enum\SerializationGroupEnum;
use App\Service\Filter\ArticleFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
* @Route("/article")
*/
class SearchController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"GET"})
     */
    public function handle(
        Request $request, ResponseService $responseService, ArticleFilterService $articleFilterService,
        EntityManagerInterface $em
    )
    {
        if($this->getUser() && $this->getUser()->getUnreadNews() > 0)
        {
            $this->getUser()->setUnreadNews(0);
            $em->flush();
        }

        return $responseService->success(
            $articleFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::ARTICLE ]
        );
    }
}
