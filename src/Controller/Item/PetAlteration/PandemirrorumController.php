<?php
declare(strict_types=1);

namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\MeritRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/pandemirrorum")]
class PandemirrorumController extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function usePandemirrorum(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pandemirrorum');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $invertedMerit = MeritRepository::findOneByName($em, MeritEnum::INVERTED);
        $veryInvertedMerit = MeritRepository::findOneByName($em, MeritEnum::VERY_INVERTED);

        if($pet->hasMerit(MeritEnum::INVERTED))
        {
            $pet->removeMerit($invertedMerit);
            $pet->addMerit($veryInvertedMerit);
            $messageExtra = $pet->getName() . ' has become VERY Inverted!';
        }
        else if($pet->hasMerit(MeritEnum::VERY_INVERTED))
        {
            $pet->removeMerit($veryInvertedMerit);
            $messageExtra = $pet->getName() . ' is no longer inverted at all!';
        }
        else
        {
            $pet->addMerit($invertedMerit);
            $messageExtra = $pet->getName() . ' has become Inverted!';
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->addFlashMessage($pet->getName() . ' stared so hard at the mirror, it shattered! ' . $messageExtra);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
