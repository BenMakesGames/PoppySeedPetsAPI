<?php
namespace App\Controller\Article;

use App\Controller\PoppySeedPetsController;
use App\Enum\SerializationGroupEnum;
use App\Repository\ArticleRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @Route("/article")
*/
class GetLatestController extends PoppySeedPetsController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/latest", methods={"GET"})
     */
    public function getLatest(
        ResponseService $responseService, ArticleRepository $articleRepository, EntityManagerInterface $em
    )
    {
        if($this->getUser() && $this->getUser()->getUnreadNews() === 1)
        {
            $this->getUser()->setUnreadNews(0);
            $em->flush();
        }

        return $responseService->success(
            $articleRepository->findOneBy([], [ 'createdOn' => 'DESC' ]),
            [ SerializationGroupEnum::ARTICLE ]
        );
    }
}
