<?php
declare(strict_types=1);

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/blushOfLife")]
class BlushOfLifeController extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function drinkBlushOfLife(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'blushOfLife');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user,
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        $merit = MeritRepository::findOneByName($em, MeritEnum::BLUSH_OF_LIFE);

        if($pet->hasMerit(MeritEnum::BLUSH_OF_LIFE))
            throw new PSPFormValidationException($pet->getName() . ' already has the Blush of Life!');

        $pet->addMerit($merit);

        $em->remove($inventory);
        $em->flush();

        PetActivityLogFactory::createUnreadLog($em, $pet, ActivityHelpers::UserName($user, true) . ' gave ' . ActivityHelpers::PetName($pet) . ' a Blush of Life to drink, granting them the Merit: Blush of Life!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
