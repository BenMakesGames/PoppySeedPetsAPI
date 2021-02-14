<?php
namespace App\Controller;

use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Model\AvailableHolidayBox;
use App\Service\InventoryService;
use App\Service\PlazaService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/plaza")
 */
class PlazaController extends PoppySeedPetsController
{
    /**
     * @Route("/availableHolidayBoxes", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailableHolidayBoxes(PlazaService $plazaService, ResponseService $responseService)
    {
        $user = $this->getUser();

        return $responseService->success(array_map(
            function(AvailableHolidayBox $box) { return $box->itemName; },
            $plazaService->getAvailableHolidayBoxes($user)
        ));
    }

    /**
     * @Route("/collectHolidayBox", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function collectHolidayBox(
        Request $request, PlazaService $plazaService,
        InventoryService $inventoryService, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $availableBoxes = $plazaService->getAvailableHolidayBoxes($user);
        $requestedBox = $request->request->get('box');

        /** @var AvailableHolidayBox $box */
        $box = ArrayFunctions::find_one($availableBoxes, function(AvailableHolidayBox $box) use($requestedBox) {
            return $box->itemName === $requestedBox;
        });

        if(!$box)
            throw new UnprocessableEntityHttpException('No holiday box is available right now...');

        if($box->itemToExchange)
        {
            if(!$inventoryService->loseItem($box->itemToExchange, $user, LocationEnum::HOME, 1))
                throw new UnprocessableEntityHttpException('You need ' . $box->itemToExchange->getNameWithArticle() . '. (Make sure it\'s in your house, not in your Basement.)');
        }

        if($box->userQuestEntity)
            $box->userQuestEntity->setValue(true);

        $inventoryService->receiveItem($box->itemName, $user, $user, $box->comment, LocationEnum::HOME, true);

        $em->flush();

        return $responseService->success(array_map(
            function(AvailableHolidayBox $box) { return $box->itemName; },
            $plazaService->getAvailableHolidayBoxes($user)
        ));
    }
}
