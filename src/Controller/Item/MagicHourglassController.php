<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use App\Service\HouseService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/magicHourglass")
 */
class MagicHourglassController extends PsyPetsItemController
{
    /**
     * @Route("/{inventory}/shatter", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function shatter(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        HouseService $houseService, UserStatsRepository $userStatsRepository, EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'magicHourglass/#/shatter');

        if($inventory->getLocation() !== LocationEnum::HOME)
        {
            return $responseService->success('Somehow you get the feeling that your pets would like to watch this happen.');
        }

        $user = $this->getUser();

        $inventoryService->receiveItem('Silica Grounds', $user, $user, $user->getName() . ' smashed a ' . $inventory->getItem()->getName() . ', spilling these Silica Grounds on the floor.', $inventory->getLocation());

        $message = 'Crazy-magic energies flow through the house, swirling and dancing with chaotic shapes that you\'re pretty sure are fractal in nature.\n\nAlso, you got Silica Grounds all over the floor.';

        if(mt_rand(1, 8) === 1)
            $message .= ' Way to go.';

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::MAGIC_HOURGLASSES_SMASHED);

        $em->getConnection()->executeQuery(
            'UPDATE pet SET `time` = `time` + 600 WHERE owner_id=:ownerId AND in_daycare=0 AND `time` < 4320',
            [ 'ownerId' => $user->getId() ]
        );

        $em->flush();

        $houseService->run($user);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}