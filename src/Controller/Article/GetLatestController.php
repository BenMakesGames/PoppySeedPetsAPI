<?php
declare(strict_types=1);

namespace App\Controller\Article;

use App\Entity\Article;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
* @Route("/article")
*/
class GetLatestController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/latest", methods={"GET"})
     */
    public function getLatest(
        ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if($user && $user->getUnreadNews() === 1)
        {
            $user->setUnreadNews(0);
            $em->flush();
        }

        $latestArticle = $em->getRepository(Article::class)->findOneBy([], [ 'createdOn' => 'DESC' ]);

        return $responseService->success(
            $latestArticle,
            [ SerializationGroupEnum::ARTICLE ]
        );
    }
}
