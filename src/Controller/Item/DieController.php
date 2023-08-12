<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function roll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        HollowEarthService $hollowEarthService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'die/#/roll');

        $itemName = $inventory->getItem()->getName();

        if(!array_key_exists($itemName, HollowEarthService::DICE_ITEMS))
            throw new PSPInvalidOperationException('The selected item is not a die!? (Weird! Reload and try again??)');

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

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::HollowEarth))
            return $responseService->itemActionSuccess('You got a ' . $roll . '.', []);

        $hollowEarthService->unlockHollowEarth($user);

        $em->flush();

        if($inventory->getLocation() === LocationEnum::BASEMENT)
            $location = 'under the basement stairs';
        else
            $location = 'on one of the walls';

        return $responseService->itemActionSuccess("You rolled a $roll.\n\nYou notice a door $location that you're _quite_ certain did not exist before now...\n\nThat's... more than a little weird.\n\n(A new location has been made available - check the menu...)");
    }
    /**
     * @Route("/{inventory}/changeYourFate", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function changeYourFate(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        HollowEarthService $hollowEarthService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'die/#/changeYourFate');

        $user->setFate();

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess("You throw the die, and it vanishes the instant before it hits the ground!\n\n_You_ feel the same, but the world around you seems... different.\n\n(Your daily trades & pet shelter offerings have been changed!)");
    }
}
