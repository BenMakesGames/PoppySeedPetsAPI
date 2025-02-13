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
class BeehiveBlueprintController extends AbstractController
{
    #[Route("/beehiveBlueprint/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function buildBeehive(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        BeehiveService $beehiveService, PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'beehiveBlueprint');

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
            throw new PSPInvalidOperationException('You\'ve already got a Beehive!');

        $magnifyingGlass = InventoryRepository::findAnyOneFromItemGroup($em, $user, 'Magnifying Glass', [
            LocationEnum::HOME,
            LocationEnum::BASEMENT,
            LocationEnum::MANTLE,
            LocationEnum::WARDROBE,
        ]);

        if(!$magnifyingGlass)
        {
            throw new PSPInvalidOperationException('Goodness! It\'s so small! You\'ll need a magnifying glass of some kind...');
        }

        $pet = BlueprintHelpers::getPet($em, $user, $request);

        $em->remove($inventory);

        UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::Beehive);

        if($user->getGreenhouse())
            $user->getGreenhouse()->setBeesDismissedOn(new \DateTimeImmutable());

        $beehiveService->createBeehive($user);

        $your = 'your';

        if($magnifyingGlass->getHolder())
            $your = $magnifyingGlass->getHolder()->getName() . '\'s';
        else if($magnifyingGlass->getWearer())
            $your = $magnifyingGlass->getWearer()->getName() . '\'s';

        BlueprintHelpers::rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            'The blueprint is _super_ tiny, but with the help of ' . $your . ' ' . $magnifyingGlass->getFullItemName() . ', you\'re able to make it all out, and you and ' . $pet->getName() . ' put the thing together! ("Beehive" has been added to the menu!)',
            $pet->getName() . ' put a Beehive together with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
