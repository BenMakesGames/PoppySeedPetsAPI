<?php
namespace App\Controller\Item\Blueprint;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Greenhouse;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Service\BeehiveService;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item")
 */
class BlueprintController extends PoppySeedPetsItemController
{
    /**
     * @Route("/basementBlueprint/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBasement(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, PetService $petService
    )
    {
        $this->validateInventory($inventory, 'basementBlueprint');

        $user = $this->getUser();

        if($user->getUnlockedBasement())
        {
            return $responseService->itemActionSuccess('You\'ve already got a Basement!');
        }
        else
        {
            $pet = $this->getPet($request, $petRepository);

            $user->setUnlockedBasement();
            $em->remove($inventory);

            $this->rewardHelper(
                $petService, $responseService,
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

    /**
     * @Route("/beehiveBlueprint/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBeehive(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        InventoryRepository $inventoryRepository, BeehiveService $beehiveService, PetService $petService,
        PetRepository $petRepository
    )
    {
        $this->validateInventory($inventory, 'beehiveBlueprint');

        $user = $this->getUser();

        $magnifyingGlasses = [
            '"Rustic" Magnifying Glass',
            'Elvish Magnifying Glass',
            'Rijndael',
            'Shiny Pail',
            'Upside-down Shiny Pail'
        ];

        if($user->getUnlockedBeehive())
        {
            throw new UnprocessableEntityHttpException('You\'ve already got a Beehive!');
        }
        else if(!$inventoryRepository->userHasAnyOneOf($user, $magnifyingGlasses, [
            LocationEnum::HOME,
            LocationEnum::BASEMENT,
            LocationEnum::MANTLE,
            LocationEnum::WARDROBE,
        ]))
        {
            throw new UnprocessableEntityHttpException('Goodness! It\'s so small! You\'ll need a magnifying glass of some kind...');
        }
        else
        {
            $pet = $this->getPet($request, $petRepository);

            $em->remove($inventory);

            $user->setUnlockedBeehive();

            $beehiveService->createBeehive($user);

            $this->rewardHelper(
                $petService, $responseService,
                $pet,
                PetSkillEnum::CRAFTS,
                'The blueprint is _super_ tiny, but with the help of a magnifying glass, you\'re able to make it all out, and you and ' . $pet->getName() . ' put the thing together! ("Beehive" has been added to the menu!)',
                $pet->getName() . ' put a Beehive together with ' . $user->getName() . '!'
            );

            $em->flush();

            return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
        }
    }

    /**
     * @Route("/greenhouseDeed/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function claim(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, PetService $petService
    )
    {
        $this->validateInventory($inventory, 'greenhouseDeed');

        $user = $this->getUser();

        if($user->getGreenhouse())
        {
            return $responseService->itemActionSuccess('You\'ve already claimed a plot in the Greenhouse.');
        }
        else
        {
            $pet = $this->getPet($request, $petRepository);

            $greenhouse = new Greenhouse();

            $user
                ->setUnlockedGreenhouse()
                ->setGreenhouse($greenhouse)
            ;

            $this->rewardHelper(
                $petService, $responseService,
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
    }

    /**
     * @Route("/birdBathBlueprint/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBirdBath(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, InventoryRepository $inventoryRepository, PetService $petService
    )
    {
        $this->validateInventory($inventory, 'birdBathBlueprint');

        $user = $this->getUser();

        if(!$user->getUnlockedGreenhouse())
            return $responseService->error(400, [ 'You need a Greenhouse to build a Bird Bath!' ]);
        else if($user->getGreenhouse()->getHasBirdBath())
            return $responseService->error(200, [ 'Your Greenhouse already has a Bird Bath!' ]);
        else
        {
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
                $petService, $responseService,
                $pet,
                PetSkillEnum::CRAFTS,
                $flashMessage,
                $pet->getName() . ' built a Bird Bath in the Greenhouse with ' . $user->getName() . '!'
            );

            $em->flush();

            return $responseService->itemActionSuccess(
                null,
                [ 'reloadInventory' => true, 'itemDeleted' => true ]
            );
        }
    }

    private function getPet(Request $request, PetRepository $petRepository): Pet
    {
        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new NotFoundHttpException('There is no such pet.');

        return $pet;
    }

    private function rewardHelper(PetService $petService, ResponseService $responseService, Pet $pet, string $skill, string $flashMessage, string $logMessage)
    {
        $changes = new PetChanges($pet);

        $pet->getSkills()->increaseStat($skill);
        $pet
            ->increaseLove(mt_rand(3, 6))
            ->increaseEsteem(mt_rand(2, 4))
        ;

        $petService->gainAffection($pet, 10);

        $responseService->addFlashMessage((new PetActivityLog())->setEntry($flashMessage));

        $responseService->createActivityLog($pet, $logMessage, '', $changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

    }
}
