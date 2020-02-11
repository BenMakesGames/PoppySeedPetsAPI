<?php
namespace App\Controller\Item\Blueprint;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/birdBathBlueprint")
 */
class BirdBathBlueprintController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBasement(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, InventoryRepository $inventoryRepository, PetService $petService
    )
    {
        $this->validateInventory($inventory, 'birdBathBlueprint');

        $user = $this->getUser();
        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$user->getUnlockedGreenhouse())
            return $responseService->error(400, [ 'You need a Greenhouse to build a Bird Bath!' ]);
        else if($user->getGreenhouse()->getHasBirdBath())
            return $responseService->error(200, [ 'Your Greenhouse already has a Bird Bath!' ]);
        else
        {
            $ironBar = $inventoryRepository->findOneToConsume($user, 'Iron Bar');

            if(!$ironBar)
                return $responseService->error(422, [ 'Hm... you\'re going to need an Iron Bar to make this...' ]);

            $em->remove($ironBar);
            $em->remove($inventory);

            $user->getGreenhouse()->setHasBirdBath(true);

            $changes = new PetChanges($pet);

            $pet->getSkills()->increaseStat(PetSkillEnum::CRAFTS);
            $pet
                ->increaseLove(mt_rand(3, 6))
                ->increaseEsteem(mt_rand(2, 4))
            ;

            $petService->gainAffection($pet, 10);

            $em->flush();

            $message = 'You build a Bird Bath with ' . $pet->getName() . '!';

            if(strtolower($pet->getName()[0]) === 'b')
                $message .= ' (How alliterative!)';

            $responseService->addActivityLog((new PetActivityLog())->setEntry($message));

            $responseService->createActivityLog($pet, $pet->getName() . ' built a Bird Bath in the Greenhouse with ' . $user->getName() . '!', '', $changes->compare($pet))
                ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
            ;

            return $responseService->itemActionSuccess(
                null,
                [ 'reloadInventory' => true, 'itemDeleted' => true ]
            );
        }
    }
}
