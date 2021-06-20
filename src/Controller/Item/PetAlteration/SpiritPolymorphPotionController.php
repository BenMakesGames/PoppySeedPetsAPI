<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Entity\SpiritCompanion;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
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
        PetRepository $petRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'spiritPolymorphPotion');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$pet->getSpiritCompanion())
            throw new UnprocessableEntityHttpException($pet->getName() . ' doesn\'t have a spirit companion! Let\'s not waste a perfectly-good potion!');

        $em->remove($inventory);

        $currentImage = $pet->getSpiritCompanion()->getImage();
        $possibleImages = array_filter(SpiritCompanion::IMAGES, fn($i) => $i !== $currentImage);

        $pet->getSpiritCompanion()->setImage($squirrel3->rngNextFromArray($possibleImages));

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
