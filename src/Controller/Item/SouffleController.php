<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\EnchantmentRepository;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Service\HattierService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/souffle")]
class SouffleController extends AbstractController
{
    #[Route("/{inventory}/startle", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function startle(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserStatsService $userStatsService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'souffle/#/startle');

        $inventory->changeItem(ItemRepository::findOneByName($em, 'Startled Soufflé'));
        $userStatsService->incrementStat($user, 'Soufflés Startled');

        $em->flush();

        $responseService->setReloadInventory(true);

        return $responseService->itemActionSuccess('It\'s _your_ hot Soufflé - you can do what you want.', ['itemDeleted' => true]);
    }
}
