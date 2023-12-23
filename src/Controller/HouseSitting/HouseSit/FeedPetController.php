<?php
namespace App\Controller\HouseSitting\HouseSit;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\HouseSittingHelpers;
use App\Functions\SimpleDb;
use App\Service\PetActivity\EatingService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/houseSit")]
class FeedPetController extends AbstractController
{
    #[Route("/{houseSitForId}/pets/{pet}/feed", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function feedPet(
        int $houseSitForId, Pet $pet, Request $request, EntityManagerInterface $em, EatingService $eatingService,
        ResponseService $responseService
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

        $items = $request->request->all('items');

        $inventory = $em->getRepository(Inventory::class)->findBy([
            'owner' => $houseSitForId,
            'id' => $items,
            'location' => LocationEnum::HOME,
        ]);

        if(count($items) !== count($inventory))
            throw new PSPNotFoundException('At least one of the items selected doesn\'t seem to exist?? (Reload and try again...)');

        $eatingService->doFeed($user, $pet, $inventory);

        $em->flush();

        return $responseService->success(
            $pet,
            [ SerializationGroupEnum::HOUSE_SITTER_PET ]
        );
    }
}