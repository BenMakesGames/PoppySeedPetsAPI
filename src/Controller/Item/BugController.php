<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Repository\ItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/bug")
 */
class BugController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/squish", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function squishBug(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/squish');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_SQUISHED);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/putOutside", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function putBugOutside(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/putOutside');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE);
        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_THROWN_AWAY);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}