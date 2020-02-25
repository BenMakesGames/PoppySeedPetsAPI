<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/hyperchromaticPrism")
 */
class HyperchromaticPrismController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function shinePrism(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, MeritRepository $meritRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'hyperchromaticPrism');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->hasMerit(MeritEnum::HYPERCHROMATIC))
            throw new UnprocessableEntityHttpException($pet->getName() . ' is already Hyperchromatic!');

        $pet->addMerit($meritRepository->findOneByName(MeritEnum::HYPERCHROMATIC));

        $em->remove($inventory);
        $em->flush();

        $responseService->addActivityLog((new PetActivityLog())->setEntry($pet->getName() . ' glows, briefly... (they\'re now Hyperchromatic!)'));

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
