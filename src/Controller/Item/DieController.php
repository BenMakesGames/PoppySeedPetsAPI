<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/die")
 */
class DieController extends PsyPetsItemController
{
    /**
     * @Route("/{inventory}/roll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em)
    {
        $this->validateInventory($inventory, 'die/#/roll');

        $user = $this->getUser();
        $itemName = $inventory->getItem()->getName();

        switch($itemName)
        {
            case 'Glowing Four-sided Die': $roll = mt_rand(1, 4); break;
            case 'Glowing Six-sided Die': $roll = mt_rand(1, 6); break;
            case 'Glowing Eight-sided Die': $roll = mt_rand(1, 8); break;
            default: throw new UnprocessableEntityHttpException('Selected item is not a die!');
        }

        if($user->getUnlockedHollowEarth() !== null)
            return $responseService->itemActionSuccess('You rolled a ' . $roll . '.', []);

        $user->setUnlockedHollowEarth();

        $em->flush();

        if($inventory->getLocation() === LocationEnum::BASEMENT)
            $location = 'under the basement stairs';
        else
            $location = 'on one of the walls';

        return $responseService->itemActionSuccess("You rolled a $roll.\n\nYou notice a door ' . $location . ' that you're _quite_ certain did not exist before now...\n\nThat's... more than a little weird.\n\n(A new location has been made available - check the menu...)");
    }
}