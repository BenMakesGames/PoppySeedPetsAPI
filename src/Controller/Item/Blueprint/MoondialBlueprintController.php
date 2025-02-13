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
class MoondialBlueprintController extends AbstractController
{
    #[Route("/moondialBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildBirdBath(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'moondialBlueprint');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return $responseService->error(400, [ 'You need a Greenhouse to build a Moondial!' ]);

        if($user->getGreenhouse()->getHasBirdBath())
            return $responseService->error(200, [ 'Your Greenhouse already has a Moondial!' ]);

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $blackonite = InventoryRepository::findOneToConsume($em, $user, 'Blackonite');
        $rock = InventoryRepository::findOneToConsume($em, $user, 'Rock');

        if(!$blackonite || !$rock)
            return $responseService->error(422, [ 'Hm... you\'re going to need a Rock, and some Blackonite to make this...' ]);

        $em->remove($blackonite);
        $em->remove($rock);
        $em->remove($inventory);

        $user->getGreenhouse()->setHasMoondial(true);

        $flashMessage = 'You build a Moondial with ' . $pet->getName() . '!';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            $flashMessage,
            $pet->getName() . ' built a Moondial in the Greenhouse with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }
}
