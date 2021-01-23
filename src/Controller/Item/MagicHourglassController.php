<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Repository\UserStatsRepository;
use App\Service\HouseService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/magicHourglass")
 */
class MagicHourglassController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/shatter", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function shatter(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        HouseService $houseService, UserStatsRepository $userStatsRepository, EntityManagerInterface $em,
        Squirrel3 $squirrel3
    )
    {
        $this->validateInventory($inventory, 'magicHourglass/#/shatter');

        if($inventory->getLocation() !== LocationEnum::HOME)
        {
            return $responseService->success('Somehow you get the feeling that your pets would like to watch this happen.');
        }

        $user = $this->getUser();

        $inventoryService->receiveItem('Aging Powder', $user, $user, $user->getName() . ' smashed a ' . $inventory->getItem()->getName() . ', spilling what was once Silica Grounds on the floor.', $inventory->getLocation());

        $message = 'Crazy-magic energies flow through the house, swirling and dancing with chaotic shapes that you\'re pretty sure are fractal in nature.' . "\n\n" . 'Also, the Silica Grounds inside - now reduced to Aging Powder - spill all over the ground.';

        if($squirrel3->rngNextInt(1, 8) === 1)
            $message .= ' Way to go.';

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::MAGIC_HOURGLASSES_SMASHED);

        $query = $em->createQuery('
            UPDATE App\Entity\PetHouseTime AS ht
            SET ht.activityTime = ht.activityTime + 600
            WHERE
                ht.activityTime < 4320
                AND ht.pet IN (
                    SELECT p.id FROM App\Entity\Pet AS p
                    WHERE p.owner=:ownerId
                    AND p.inDaycare=0
                )
        ');

        $query->execute([
            'ownerId' => $user->getId()
        ]);

        $em->flush();

        $houseService->run($user);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
