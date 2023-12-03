<?php
namespace App\Controller\Achievement;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\InMemoryCache;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/achievement")]
final class Available extends AbstractController
{
    #[Route("/available", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAvailable(
        ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $claimed = $em->getRepository(UserBadge::class)->createQueryBuilder('b')
            ->select('b.badge')
            ->andWhere('b.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult()
        ;

        $unclaimed = array_diff(BadgeEnum::getValues(), $claimed);

        $info = [];
        $cache = new InMemoryCache();

        foreach($unclaimed as $badge)
            $info[] = BadgeHelpers::getBadgeProgress($badge, $user, $em, $cache);

        return $responseService->success($info, [ SerializationGroupEnum::TRADER_OFFER, SerializationGroupEnum::MARKET_ITEM ]);
    }
}