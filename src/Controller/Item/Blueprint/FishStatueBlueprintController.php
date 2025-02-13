<?php
declare(strict_types=1);

namespace App\Controller\Item\Blueprint;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Greenhouse;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Service\BeehiveService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item")]
class FishStatueBlueprintController extends AbstractController
{
    #[Route("/fishStatue/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function installFishStatue(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fishStatue');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return $responseService->error(400, [ 'You need a Greenhouse to install a Fish Statue!' ]);

        if($user->getGreenhouse()->isHasFishStatue())
            return $responseService->error(200, [ 'Your Greenhouse already has a Fish State!' ]);

        $pet = BlueprintHelpers::getPet($em, $this->getUser(), $request);

        $threeDeePrinterId = ItemRepository::getIdByName($em, '3D Printer');

        if(InventoryService::countInventory($em, $user->getId(), $threeDeePrinterId, LocationEnum::HOME) < 1)
            return $responseService->itemActionSuccess('The statue appears to be a fountain! You and ' . $pet->getName() . ' are going to need a 3D Printer at home, and some Plastic to make some pipes...');

        $plastic = InventoryRepository::findOneToConsume($em, $user, 'Plastic');

        if(!$plastic)
            return $responseService->itemActionSuccess('The statue appears to be a fountain! You and ' . $pet->getName() . ' are going to need a 3D Printer at home, and some Plastic to make some pipes...');

        $em->remove($plastic);
        $em->remove($inventory);

        $user->getGreenhouse()->setHasFishStatue(true);

        $flashMessage = 'You install a Fish Statue with ' . $pet->getName() . '!';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            $flashMessage,
            $pet->getName() . ' installed a Fish Statue in the Greenhouse with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
