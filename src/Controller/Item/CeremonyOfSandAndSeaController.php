<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/ceremonyOfSandAndSea")
 */
class CeremonyOfSandAndSeaController extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useItem(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserQuestRepository $userQuestRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ceremonyOfSandAndSea/#');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            throw new PSPNotUnlockedException('Greenhouse');

        $expandedGreenhouseWithShovel = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse with Ceremony of Sand and Sea', false);

        if($expandedGreenhouseWithShovel->getValue())
            throw new PSPInvalidOperationException('The sea has already been summoned to your Greenhouse!');

        $expandedGreenhouseWithShovel->setValue(true);

        $user->getGreenhouse()->increaseMaxWaterPlants(2);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Ceremonial Trident'))
            ->setModifiedOn()
        ;

        $em->flush();
        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess('Water pours from the trident, adding two new water plots to your Greenhouse! The trident, having spent its magic, is reduced to a regular ol\' Ceremonial Trident.', [ 'itemDeleted' => true ]);
    }
}
