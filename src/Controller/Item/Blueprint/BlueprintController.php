<?php
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
use App\Functions\PetActivityLogFactory;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Service\BeehiveService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class BlueprintController extends AbstractController
{
    /**
     * @Route("/installComposter/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function installComposter(
        Inventory $inventory, ResponseService $responseService, PetRepository $petRepository, Request $request,
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

        $pet = $this->getPet($request, $petRepository);

        $em->remove($inventory);

        $user->getGreenhouse()->setHasComposter(true);

        $flashMessage = 'You install the Composter with ' . $pet->getName() . '!';

        $this->rewardHelper(
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

    /**
     * @Route("/basementBlueprint/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBasement(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'basementBlueprint');

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Basement))
            return $responseService->itemActionSuccess('You\'ve already got a Basement!');

        $pet = $this->getPet($request, $petRepository);

        UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::Basement);

        $em->remove($inventory);

        $this->rewardHelper(
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

    /**
     * @Route("/beehiveBlueprint/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBeehive(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        InventoryRepository $inventoryRepository, BeehiveService $beehiveService, PetExperienceService $petExperienceService,
        PetRepository $petRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'beehiveBlueprint');

        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
            throw new PSPInvalidOperationException('You\'ve already got a Beehive!');

        $magnifyingGlass = $inventoryRepository->findAnyOneFromItemGroup($user, 'Magnifying Glass', [
            LocationEnum::HOME,
            LocationEnum::BASEMENT,
            LocationEnum::MANTLE,
            LocationEnum::WARDROBE,
        ]);

        if(!$magnifyingGlass)
        {
            throw new PSPInvalidOperationException('Goodness! It\'s so small! You\'ll need a magnifying glass of some kind...');
        }

        $pet = $this->getPet($request, $petRepository);

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

        $this->rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            'The blueprint is _super_ tiny, but with the help of ' . $your . ' ' . $magnifyingGlass->getFullItemName() . ', you\'re able to make it all out, and you and ' . $pet->getName() . ' put the thing together! ("Beehive" has been added to the menu!)',
            $pet->getName() . ' put a Beehive together with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/greenhouseDeed/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claim(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'greenhouseDeed');

        if($user->getGreenhouse())
        {
            throw new PSPInvalidOperationException('You\'ve already claimed a plot in the Greenhouse.');
        }

        $pet = $this->getPet($request, $petRepository);

        $greenhouse = new Greenhouse();

        $user->setGreenhouse($greenhouse);

        UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::Greenhouse);

        $this->rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            'You and ' . $pet->getName() . ' clear out a space in the public Greenhouse! ("Greenhouse" has been added to the menu!)',
            $pet->getName() . ' cleared out a space in the public Greenhouse with ' . $user->getName() . '!'
        );

        $em->persist($greenhouse);
        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/birdBathBlueprint/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBirdBath(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, InventoryRepository $inventoryRepository, PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'birdBathBlueprint');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return $responseService->error(400, [ 'You need a Greenhouse to build a Bird Bath!' ]);

        if($user->getGreenhouse()->getHasBirdBath())
            return $responseService->error(200, [ 'Your Greenhouse already has a Bird Bath!' ]);

        $pet = $this->getPet($request, $petRepository);

        $ironBar = $inventoryRepository->findOneToConsume($user, 'Iron Bar');

        if(!$ironBar)
            return $responseService->error(422, [ 'Hm... you\'re going to need an Iron Bar to make this...' ]);

        $em->remove($ironBar);
        $em->remove($inventory);

        $user->getGreenhouse()->setHasBirdBath(true);

        $flashMessage = 'You build a Bird Bath with ' . $pet->getName() . '!';

        if(strtolower($pet->getName()[0]) === 'b')
            $flashMessage .= ' (How alliterative!)';

        $this->rewardHelper(
            $petExperienceService, $responseService, $em,
            $pet,
            PetSkillEnum::CRAFTS,
            $flashMessage,
            $pet->getName() . ' built a Bird Bath in the Greenhouse with ' . $user->getName() . '!'
        );

        $em->flush();

        return $responseService->itemActionSuccess(
            null,
            [ 'itemDeleted' => true ]
        );
    }

    /**
     * @Route("/fishStatue/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function installFishStatue(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, InventoryRepository $inventoryRepository, PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fishStatue');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            return $responseService->error(400, [ 'You need a Greenhouse to install a Fish Statue!' ]);

        if($user->getGreenhouse()->isHasFishStatue())
            return $responseService->error(200, [ 'Your Greenhouse already has a Fish State!' ]);

        $pet = $this->getPet($request, $petRepository);

        $threeDeePrinterId = ItemRepository::getIdByName($em, '3D Printer');

        if(InventoryService::countInventory($em, $user->getId(), $threeDeePrinterId, LocationEnum::HOME) < 1)
            return $responseService->itemActionSuccess('The statue appears to be a fountain! You and ' . $pet->getName() . ' are going to need a 3D Printer at home, and some Plastic to make some pipes...');

        $plastic = $inventoryRepository->findOneToConsume($user, 'Plastic');

        if(!$plastic)
            return $responseService->itemActionSuccess('The statue appears to be a fountain! You and ' . $pet->getName() . ' are going to need a 3D Printer at home, and some Plastic to make some pipes...');

        $em->remove($plastic);
        $em->remove($inventory);

        $user->getGreenhouse()->setHasFishStatue(true);

        $flashMessage = 'You install a Fish Statue with ' . $pet->getName() . '!';

        $this->rewardHelper(
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

    private function getPet(Request $request, PetRepository $petRepository): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        return $pet;
    }

    private function rewardHelper(
        PetExperienceService $petExperienceService, ResponseService $responseService,
        EntityManagerInterface $em, Pet $pet, ?string $skill, string $flashMessage, string $logMessage
    )
    {
        $squirrel3 = new Squirrel3();
        $changes = new PetChanges($pet);

        if($skill && $pet->getSkills()->getStat($skill) >= 20)
            $skill = null;

        $pet
            ->increaseLove($squirrel3->rngNextInt(3, 6))
            ->increaseEsteem($squirrel3->rngNextInt(2, 4))
        ;

        $petExperienceService->gainAffection($pet, 10);

        $responseService->addFlashMessage($flashMessage);

        $activityLog = PetActivityLogFactory::createUnreadLog($em, $pet, $logMessage)
            ->setIcon('ui/affection')
            ->setChanges($changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        if($skill)
        {
            $pet->getSkills()->increaseStat($skill);

            $activityLog
                ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
                ->setEntry($activityLog->getEntry() . ' +1 ' . ucfirst($skill) . '!')
                ->addTags(PetActivityLogTagRepository::findByNames($em, [ 'Level-up' ]));
        }
    }
}
