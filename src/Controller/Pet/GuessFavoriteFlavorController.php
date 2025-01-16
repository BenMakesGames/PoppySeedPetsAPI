<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetBadgeHelpers;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class GuessFavoriteFlavorController extends AbstractController
{
    #[Route("/{pet}/guessFavoriteFlavor", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function guessFavoriteFlavor(
        Pet $pet, Request $request, ResponseService $responseService,
        InventoryService $inventoryService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It\'s not interested in revealing its favorite flavor to you.');

        if($pet->getRevealedFavoriteFlavor())
            throw new PSPInvalidOperationException($pet->getName() . '\'s favorite flavor has already been revealed!');

        $guess = strtolower(trim($request->request->getAlpha('flavor')));

        if(!FlavorEnum::isAValue($guess))
            throw new PSPFormValidationException('Please pick a flavor.');

        $flavorGuesses = UserQuestRepository::findOrCreate($em, $user, 'Flavor Guesses for Pet #' . $pet->getId(), 0);

        if($flavorGuesses->getValue() > 0 && $flavorGuesses->getLastUpdated()->format('Y-m-d') === date('Y-m-d'))
            throw new PSPInvalidOperationException('You already guessed today. Try again tomorrow.');

        $flavorGuesses->setValue($flavorGuesses->getValue() + 1);

        $data = null;

        if($pet->getFavoriteFlavor() === $guess)
        {
            $pet
                ->setRevealedFavoriteFlavor($flavorGuesses->getValue())
                ->increaseAffectionLevel(1)
            ;
            $inventoryService->receiveItem('Heartstone', $user, $user, $user->getName() . ' received this from ' . $pet->getName() . ' for knowing their favorite flavor: ' . $pet->getFavoriteFlavor() . '!', LocationEnum::HOME);
            $responseService->setReloadInventory();
            $data = $pet;

            PetBadgeHelpers::awardBadgeAndLog($em, $pet, PetBadgeEnum::REVEALED_FAVORITE_FLAVOR, $user->getName() . ' correctly guessed ' . $pet->getName() . '\'s favorite flavor! A Heartstone materialized in front of their body, and floated into ' . $user->getName() . '\'s hands!');
        }
        else
        {
            $responseService->addFlashMessage('Hm... it seems that wasn\'t correct. ' . $pet->getName() . ' looks a little disappointed. (You can try again, tomorrow.)');
        }

        $em->flush();

        return $responseService->success($data, [ SerializationGroupEnum::MY_PET ]);
    }
}
