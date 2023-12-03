<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\IRandom;
use App\Service\PetActivity\EatingService;
use App\Service\PetAndPraiseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class PetAndFeedController extends AbstractController
{
    /**
     * @Route("/{pet}/pet", methods={"POST"}, requirements={"pet"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function pet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        PetAndPraiseService $petAndPraiseService
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->isAtHome())
            throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        $petAndPraiseService->doPet($pet);

        $em->flush();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
        {
            return $responseService->success([ 'pet' => $pet ], [ SerializationGroupEnum::MY_PET ]);
        }
        else
        {
            $emojis = $pet->getAffectionExpressions();
            $emoji = \mb_substr($emojis, $rng->rngNextInt(0, \mb_strlen($emojis) - 1), 1);

            return $responseService->success([ 'pet' => $pet, 'emoji' => $emoji ], [ SerializationGroupEnum::MY_PET ]);
        }
    }

    /**
     * @Route("/{pet}/feed", methods={"POST"}, requirements={"pet"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function feed(
        Pet $pet, Request $request, InventoryRepository $inventoryRepository, ResponseService $responseService,
        EntityManagerInterface $em, EatingService $eatingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->isAtHome())
            throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        $items = $request->request->all('items');

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $items,
            'location' => LocationEnum::HOME,
        ]);

        if(count($items) !== count($inventory))
            throw new PSPNotFoundException('At least one of the items selected doesn\'t seem to exist?? (Reload and try again...)');

        $eatingService->doFeed($pet, $inventory);

        $em->flush();

        return $responseService->success(
            $pet,
            [ SerializationGroupEnum::MY_PET ]
        );
    }
}
