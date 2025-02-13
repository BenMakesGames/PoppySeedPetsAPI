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
class ComposterBlueprintController extends AbstractController
{
    #[Route("/installComposter/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function installComposter(
        Inventory $inventory, ResponseService $responseService, Request $request,
        PetExperienceService $petExperienceService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'installComposter');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return $responseService->error(400, [ 'You need a Greenhouse to install a Composter!' ]);

        if($user->getGreenhouse()->getHasComposter())
            return $responseService->error(200, [ 'Your Greenhouse already has a Composter!' ]);

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $em->remove($inventory);

        $user->getGreenhouse()->setHasComposter(true);

        $flashMessage = 'You install the Composter with ' . $pet->getName() . '!';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            null,
            $flashMessage,
            $pet->getName() . ' installed a Composter in the Greenhouse with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
