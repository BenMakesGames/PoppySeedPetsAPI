<?php
declare(strict_types=1);

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\MeritRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/hyperchromaticPrism")]
class HyperchromaticPrismController extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function shinePrism(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'hyperchromaticPrism');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::HYPERCHROMATIC))
            throw new PSPInvalidOperationException($pet->getName() . ' is already Hyperchromatic!');

        $pet->addMerit(MeritRepository::findOneByName($em, MeritEnum::HYPERCHROMATIC));

        $em->remove($inventory);
        $em->flush();

        $responseService->addFlashMessage($pet->getName() . ' glows, briefly... (they\'re now Hyperchromatic!)');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
