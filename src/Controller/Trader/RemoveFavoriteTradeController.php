<?php
declare(strict_types=1);

namespace App\Controller\Trader;

use App\Entity\User;
use App\Entity\UserFavoriteTrade;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\UserQuestRepository;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\TraderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/trader")]
class RemoveFavoriteTradeController extends AbstractController
{
    #[Route("/{id}/favorite", methods: ["DELETE"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function makeExchange(
        string $id, TraderService $traderService, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Trader))
            throw new PSPNotUnlockedException('Trader');

        $exchange = $traderService->getOfferById($user, $id);

        if(!$exchange)
            throw new PSPNotFoundException('There is no such exchange available.');

        $favorite = $em->getRepository(UserFavoriteTrade::class)->findOneBy([
            'user' => $user,
            'trade' => $exchange->id
        ]);

        if($favorite)
        {
            $em->remove($favorite);
            $em->flush();
        }

        return $responseService->success();
    }
}
