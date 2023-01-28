<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\StatusEffectEnum;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/werebane")
 */
class WerebaneController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function drinkWerebane(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'werebane');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE))
            throw new NotFoundHttpException('But it tastes, like, REALLY gross, and ' . $pet->getName() . ' hasn\'t been bitten by a werecreature, anyway, so... not worth!');

        $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE));

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::WEREFORM));

        $em->remove($inventory);
        $em->flush();

        $responseService->addFlashMessage($pet->getName() . '\'s blood has been cleansed! (No more werecreature saliva, or whatever was going on in there!)');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
