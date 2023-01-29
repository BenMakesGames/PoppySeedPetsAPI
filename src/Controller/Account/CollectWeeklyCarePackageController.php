<?php
namespace App\Controller\Account;

use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/account")
 */
class CollectWeeklyCarePackageController extends AbstractController
{
    /**
     * @Route("/collectWeeklyCarePackage", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function collectWeeklyBox(
        Request $request, EntityManagerInterface $em, ResponseService $responseService,
        InventoryService $inventoryService, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        $type = $request->request->getInt('type');

        $days = (new \DateTimeImmutable())->diff($user->getLastAllowanceCollected())->days;

        if($days < 7)
            throw new UnprocessableEntityHttpException('It\'s too early to collect your weekly Care Package.');

        $itemsDonated = $userStatsRepository->getStatValue($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM);

        $canGetHandicraftsBox = $itemsDonated >= 150;
        $canGetFishBag = $itemsDonated >= 450;
        $canGetGamingBox = $user->getUnlockedHollowEarth();

        if($type === 1)
        {
            $newInventory = $inventoryService->receiveItem('Fruits & Veggies Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 2)
        {
            $newInventory = $inventoryService->receiveItem('Baker\'s Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 3 && $canGetHandicraftsBox)
        {
            $newInventory = $inventoryService->receiveItem('Handicrafts Supply Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 4 && $canGetGamingBox)
        {
            $newInventory = $inventoryService->receiveItem('Gaming Box', $user, $user, $user->getName() . ' got this as a weekly Care Package.', LocationEnum::HOME, true);
        }
        else if($type === 5 && $canGetFishBag)
        {
            $newInventory = $inventoryService->receiveItem('Fish Bag', $user, $user, $user->getName() . ' got this as a weekly Care... Bag??', LocationEnum::HOME, true);
        }
        else
            throw new UnprocessableEntityHttpException('Must specify a Care Package "type".');

        $user->setLastAllowanceCollected($user->getLastAllowanceCollected()->modify('+' . (floor($days / 7) * 7) . ' days'));

        $em->flush();

        return $responseService->success($newInventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
