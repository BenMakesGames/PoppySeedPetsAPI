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
 * @Route("/item/ceremonyOfSandAndSea")
 */
class CeremonyOfSandAndSeaController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository, ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'ceremonyOfSandAndSea/#');

        $user = $this->getUser();

        if(!$user->getUnlockedGreenhouse())
            throw new UnprocessableEntityHttpException('You need a Greenhouse to summon the sea to!');

        $expandedGreenhouseWithShovel = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse with Ceremony of Sand and Sea', false);

        if($expandedGreenhouseWithShovel->getValue())
            throw new UnprocessableEntityHttpException('The sea has already been summoned to your Greenhouse!');

        $expandedGreenhouseWithShovel->setValue(true);

        $user->getGreenhouse()->increaseMaxWaterPlants(2);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($itemRepository->findOneByName('Ceremonial Trident'))
            ->setModifiedOn()
        ;

        $em->flush();

        return $responseService->itemActionSuccess('Water pours from the trident, adding two new water plots to your Greenhouse! The trident, having spent its magic, is reduced to a regular ol\' Ceremonial Trident.', [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => $reloadPets ]);
    }
}
