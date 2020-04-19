<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/gizubisShovel")
 */
class GizbuisShovelController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/dig", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function dig(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'gizubisShovel/#/dig');

        $user = $this->getUser();

        if(!$user->getUnlockedGreenhouse())
            throw new UnprocessableEntityHttpException('You don\'t have a Greenhouse to bless...');

        $expandedGreenhouseWithShovel = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse with Gizubi\'s Shovel', false);

        if($expandedGreenhouseWithShovel->getValue())
            throw new UnprocessableEntityHttpException('Your Greenhouse has already received Gizbui\'s blessings. It can\'t be blessed twice! (Don\'t be silly!)');

        $expandedGreenhouseWithShovel->setValue(true);

        $user->getGreenhouse()->increaseMaxPlants(1);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($itemRepository->findOneByName('Farmer\'s Multi-tool'))
            ->setModifiedOn()
        ;

        $em->flush();

        return $responseService->itemActionSuccess('Gizubi\'s blessing leaves the shovel, and permeates the soil of your Greenhouse. Your Greenhouse can now grow an additional plant!', [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => $reloadPets ]);
    }
}
