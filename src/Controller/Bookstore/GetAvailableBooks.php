<?php
namespace App\Controller\Bookstore;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\BookstoreService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

// allows player to buy books; inventory grows based on various criteria

#[Route("/bookstore")]
class GetAvailableBooks extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAvailableBooks(
        BookstoreService $bookstoreService, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Bookstore))
            throw new PSPNotUnlockedException('Bookstore');

        $data = $bookstoreService->getResponseData($user);

        return $responseService->success($data, [ SerializationGroupEnum::MARKET_ITEM ]);
    }
}
