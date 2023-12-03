<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/werebane")
 */
class WerebaneController extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function drinkWerebane(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'werebane');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $removedSomething = false;

        if($pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE))
        {
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::BITTEN_BY_A_VAMPIRE));
            $removedSomething = true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE))
        {
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE));
            $removedSomething = true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::WEREFORM));
            $removedSomething = true;
        }

        if(!$removedSomething)
            throw new PSPInvalidOperationException('But it tastes, like, REALLY gross, and ' . $pet->getName() . ' hasn\'t been bitten by anything supernatural, anyway, so... not worth!');

        $em->remove($inventory);
        $em->flush();

        $responseService->addFlashMessage($pet->getName() . '\'s blood has been cleansed! (No more werecreature saliva, or whatever was going on in there!)');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
