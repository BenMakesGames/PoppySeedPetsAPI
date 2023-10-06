<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ArrayFunctions;
use App\Model\AvailableHolidayBox;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\MuseumService;
use App\Service\PlazaService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/plaza")
 */
class PlazaController extends AbstractController
{
    /**
     * @Route("/collectHolidayBox", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function collectHolidayBox(
        Request $request, PlazaService $plazaService, MuseumService $museumService,
        InventoryService $inventoryService, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $availableBoxes = $plazaService->getAvailableHolidayBoxes($user);
        $requestedBox = $request->request->get('box');

        /** @var AvailableHolidayBox $box */
        $box = ArrayFunctions::find_one($availableBoxes, fn(AvailableHolidayBox $box) => $box->nameWithQuantity === $requestedBox);

        if(!$box)
            throw new PSPInvalidOperationException('No holiday box is available right now...');

        if($box->userQuestEntity)
            $box->userQuestEntity->setValue(true);

        if(strpos($box->itemName, 'Box') !== false || strpos($box->itemName, 'Bag') !== false)
            UserStatsRepository::incrementStat($em, $user, UserStatEnum::PLAZA_BOXES_RECEIVED, $box->quantity);

        for($i = 0; $i < $box->quantity; $i++)
            $inventoryService->receiveItem($box->itemName, $user, $user, $box->comment, LocationEnum::HOME, true);

        $museumService->forceDonateItem($user, $box->itemName, 'Tess donated this to the Museum on your behalf.', null);

        $em->flush();

        $responseService->addFlashMessage('Here you go! Your ' . $box->tradeDescription . '!');

        return $responseService->success(array_map(
            fn(AvailableHolidayBox $box) => $box->tradeDescription,
            $plazaService->getAvailableHolidayBoxes($user)
        ));
    }
}
