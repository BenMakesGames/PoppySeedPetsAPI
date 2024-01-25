<?php
namespace App\Controller\HouseSitting\HouseSit;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\HouseSittingHelpers;
use App\Functions\SimpleDb;
use App\Service\IRandom;
use App\Service\PetAndPraiseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/houseSit")]
class PetPetController extends AbstractController
{
    #[Route("/{houseSitForId}/pets/{pet}/pet", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function petPet(
        int $houseSitForId, Pet $pet, PetAndPraiseService $petAndPraiseService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        HouseSittingHelpers::canHouseSitOrThrow($db, $user, $houseSitForId);

        if($pet->getOwner()->getId() !== $houseSitForId)
            throw new PSPPetNotFoundException();

        if(!$pet->isAtHome())
            throw new PSPInvalidOperationException('Pets that aren\'t home cannot be interacted with.');

        $petAndPraiseService->doPet($user, $pet);

        $em->flush();

        $emoji = $pet->getRandomAffectionExpression($rng);

        if($emoji)
            return $responseService->success([ 'pet' => $pet, 'emoji' => $emoji ], [ SerializationGroupEnum::HOUSE_SITTER_PET ]);
        else
            return $responseService->success([ 'pet' => $pet ], [ SerializationGroupEnum::HOUSE_SITTER_PET ]);
    }
}