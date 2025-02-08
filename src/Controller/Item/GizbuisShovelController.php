<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ItemRepository;
use App\Functions\UserQuestRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/gizubisShovel")]
class GizbuisShovelController extends AbstractController
{
    #[Route("/{inventory}/dig", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function dig(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'gizubisShovel/#/dig');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            throw new PSPNotUnlockedException('Greenhouse');

        $expandedGreenhouseWithShovel = UserQuestRepository::findOrCreate($em, $user, 'Expanded Greenhouse with Gizubi\'s Shovel', false);

        if($expandedGreenhouseWithShovel->getValue())
            throw new PSPInvalidOperationException('Your Greenhouse has already received Gizbui\'s blessings. It can\'t be blessed twice! (Don\'t be silly!)');

        $expandedGreenhouseWithShovel->setValue(true);

        $user->getGreenhouse()->increaseMaxPlants(1);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem(ItemRepository::findOneByName($em, 'Farmer\'s Multi-tool'))
            ->setModifiedOn()
        ;

        $em->flush();

        $responseService->setReloadPets($reloadPets);

        return $responseService->itemActionSuccess('Gizubi\'s blessing leaves the shovel, and permeates the soil of your Greenhouse. Your Greenhouse can now grow an additional plant!', [ 'itemDeleted' => true ]);
    }
}
