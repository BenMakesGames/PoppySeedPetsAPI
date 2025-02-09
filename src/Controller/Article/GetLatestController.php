<?php
declare(strict_types=1);

namespace App\Controller\Article;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\Article;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/article")]
class GetLatestController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/latest", methods: ["GET"])]
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
