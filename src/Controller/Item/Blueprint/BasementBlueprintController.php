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
class BasementBlueprintController extends AbstractController
{
    #[Route("/basementBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildBasement(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'basementBlueprint');

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            return $responseService->itemActionSuccess('You\'ve already got a Basement!');

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::Basement);

        $em->remove($inventory);

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            $pet->getName() . ' helps you build the Basement. Together, you\'re done in no time! (Video game logic!) ("Basement" has been added to the menu!)',
            $pet->getName() . ' helped ' . $user->getName() . ' "build" a Basement!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
