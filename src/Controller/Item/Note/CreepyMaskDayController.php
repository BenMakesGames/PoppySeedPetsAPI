<?php
namespace App\Controller\Item\Note;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use App\Service\TraderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/note")]
class CreepyMaskDayController extends AbstractController
{
    #[Route("/creepyMaskDay/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readCreepyMaskDayNote(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'note/creepyMaskDay/#/read');

        $lines = [];

        for($i = 1; $i <= 12; $i++)
        {
            $monthName = strtolower(date("M", mktime(0, 0, 0, $i, 10)));
            $payment = TraderService::getCreepyMaskDayPayment($i);
            $item = strtolower($payment[0]);

            if($item == 'petrichor') $item .= ' (ugh!)';
            else if($item == 'wed bawwoon') $item .= ' ?';
            else if($item == 'little strongbox') $item = '~~stron~~ LITTLE strongbox';

            $quantity = $payment[1];
            if($quantity == 1)
                $lines[] = "$monthName = $item";
            else
                $lines[] = "$monthName = $item &nbsp; **x$quantity**";
        }

        $lines[] = '';
        $lines[] = 'oct-mar = ash, crystal, gold';
        $lines[] = '~~may-~~**o**thers = others';

        return $responseService->itemActionSuccess(join('<br>', $lines));
    }
}
