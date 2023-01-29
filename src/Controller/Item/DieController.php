<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/die")
 */
class DieController extends AbstractController
{
    /**
     * @Route("/{inventory}/roll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        HollowEarthService $hollowEarthService
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'die/#/roll');

        $user = $this->getUser();
        $itemName = $inventory->getItem()->getName();

        if(!array_key_exists($itemName, HollowEarthService::DICE_ITEMS))
            throw new UnprocessableEntityHttpException('Selected item is not a die!');

        if($itemName === 'Dreidel')
        {
            $roll = $squirrel3->rngNextFromArray([
                'נ', 'ג', 'ה', 'ש'
            ]);
        }
        else
        {
            $sides = HollowEarthService::DICE_ITEMS[$itemName];
            $roll = $squirrel3->rngNextInt(1, $sides);
        }

        if($user->getUnlockedHollowEarth() !== null)
            return $responseService->itemActionSuccess('You got a ' . $roll . '.', []);

        $hollowEarthService->unlockHollowEarth($user);

        $em->flush();

        if($inventory->getLocation() === LocationEnum::BASEMENT)
            $location = 'under the basement stairs';
        else
            $location = 'on one of the walls';

        return $responseService->itemActionSuccess("You rolled a $roll.\n\nYou notice a door $location that you're _quite_ certain did not exist before now...\n\nThat's... more than a little weird.\n\n(A new location has been made available - check the menu...)");
    }
}
