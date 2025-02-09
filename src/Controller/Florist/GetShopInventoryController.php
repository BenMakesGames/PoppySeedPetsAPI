<?php
declare(strict_types=1);

namespace App\Controller\Florist;

use App\Entity\Inventory;
use App\Entity\User;
use App\Entity\UserStats;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\FloristService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/florist")]
class GetShopInventoryController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getInventory(
        FloristService $floristService, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Florist))
            throw new PSPNotUnlockedException('Florist');

        $hasRolledSatyrDice = $em->getRepository(UserStats::class)->findOneBy([
            'user' => $user,
            'stat' => UserStatEnum::ROLLED_SATYR_DICE
        ]);

        return $responseService->success([
            'inventory' => $floristService->getInventory($user),
            'canTradeForGiftPackage' => $hasRolledSatyrDice !== null && $hasRolledSatyrDice->getValue() > 0
        ]);
    }
}
