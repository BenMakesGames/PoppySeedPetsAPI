<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\SpiritCompanion;
use App\Enum\MeritEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/spiritPolymorphPotion")
 */
class SpiritPolymorphPotionController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/drink", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function drink(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'spiritPolymorphPotion');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if(!$pet->getSpiritCompanion())
            throw new UnprocessableEntityHttpException($pet->getName() . ' doesn\'t have a spirit companion! Let\'s not waste a perfectly-good potion!');

        $em->remove($inventory);

        $currentImage = $pet->getSpiritCompanion()->getImage();
        $possibleImages = array_filter(SpiritCompanion::IMAGES, function($i) use($currentImage) { return $i !== $currentImage; });

        $pet->getSpiritCompanion()->setImage(ArrayFunctions::pick_one($possibleImages));

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
