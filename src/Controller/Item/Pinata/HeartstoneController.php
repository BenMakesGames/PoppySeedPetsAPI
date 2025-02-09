<?php
declare(strict_types=1);

namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/heartstone")]
class HeartstoneController extends AbstractController
{
    private const STAT_NAME = 'Transformed a Heartstone';

    #[Route("/{inventory}/transform", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function transform(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'heartstone/#/transform');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $numberTransformed = $userStatsRepository->getStatValue($user, self::STAT_NAME);
        $petsWhoHaveCompletedHeartDimensionAdventures = $userStatsRepository->getStatValue($user, 'Pet Completed the Heartstone Dimension');

        $numberThatCanBeTransformed = $petsWhoHaveCompletedHeartDimensionAdventures - $numberTransformed;

        if($numberThatCanBeTransformed <= 0)
        {
            if($numberTransformed == 0)
                throw new PSPInvalidOperationException('You cannot transform a Heartstone until one of your pets has completed all of the Heartstone Dimension challenges.');
            else
                throw new PSPInvalidOperationException('You cannot transform any more Heartstones until another one of your pets has completed all of the Heartstone Dimension challenges.');
        }

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        for($i = 0; $i < 2; $i++)
            $inventoryService->receiveItem('Heartessence', $user, $user, $user->getName() . ' got this by transforming a Heartstone.', $location, $locked);

        $userStatsRepository->incrementStat($user, self::STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('The Heartstone evaporates, releasing two Heartessences!', [ 'itemDeleted' => true ]);
    }
}
