<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/ceremonyOfSandAndSea")
 */
class CeremonyOfSandAndSeaController extends AbstractController
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
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ceremonyOfSandAndSea/#');

        if(!$user->getUnlockedGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        $expandedGreenhouseWithShovel = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse with Ceremony of Sand and Sea', false);

        if($expandedGreenhouseWithShovel->getValue())
            throw new PSPInvalidOperationException('The sea has already been summoned to your Greenhouse!');

        $expandedGreenhouseWithShovel->setValue(true);

        $user->getGreenhouse()->increaseMaxWaterPlants(2);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($itemRepository->findOneByName('Ceremonial Trident'))
            ->setModifiedOn()
        ;

        $em->flush();
        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess('Water pours from the trident, adding two new water plots to your Greenhouse! The trident, having spent its magic, is reduced to a regular ol\' Ceremonial Trident.', [ 'itemDeleted' => true ]);
    }
}
