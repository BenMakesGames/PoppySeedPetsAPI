<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ArrayFunctions;
use App\Service\Filter\PetFilterService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/pet")]
class DaycareController extends AbstractController
{
    #[Route("/daycare", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyDaycarePets(
        ResponseService $responseService, PetFilterService $petFilterService, Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petFilterService->addRequiredFilter('owner', $user->getId());
        $petFilterService->addRequiredFilter('location', [ PetLocationEnum::DAYCARE, PetLocationEnum::HOME ]);

        $petsInDaycare = $petFilterService->getResults($request->query);

        return $responseService->success(
            $petsInDaycare,
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_PET ]
        );
    }

    /**
     * @Route("/daycare/arrange", methods={"POST"})
     * @DoesNotRequireHouseHours()
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function arrangePets(
        ResponseService $responseService, Request $request, EntityManagerInterface $em
    )
    {
        $petIds = array_unique($request->request->all('pets'));

        if(ArrayFunctions::any($petIds, fn(int $id) => $id <= 0))
            throw new PSPFormValidationException('Invalid pet ID(s) provided.');

        /** @var User $user */
        $user = $this->getUser();

        $petsWantedAtHome = $em->getRepository(Pet::class)->findBy([
            'id' => $petIds,
            'owner' => $user
        ]);

        if(count($petsWantedAtHome) !== count($petIds))
            throw new PSPPetNotFoundException();

        if(count($petsWantedAtHome) > $user->getMaxPets())
            throw new PSPInvalidOperationException('You cannot have more than ' . $user->getMaxPets() . ' pets at home.');

        if(ArrayFunctions::any($petsWantedAtHome, fn(Pet $pet) => $pet->getLocation() !== PetLocationEnum::HOME && $pet->getLocation() !== PetLocationEnum::DAYCARE))
            throw new PSPInvalidOperationException('Pets may only be moved between home and/or Daycare.');

        $petsAtHome = $em->getRepository(Pet::class)->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        $petsToMoveToHome = array_filter($petsWantedAtHome, fn(Pet $pet) => $pet->getLocation() === PetLocationEnum::DAYCARE);
        $petsToMoveToDaycare = array_filter($petsAtHome, fn(Pet $pet) => !ArrayFunctions::any($petsWantedAtHome, fn(Pet $p) => $p->getId() === $pet->getId()));

        foreach($petsToMoveToHome as $pet)
            self::takePetOutOfDaycare($pet);

        foreach($petsToMoveToDaycare as $pet)
            $pet->setLocation(PetLocationEnum::DAYCARE);

        $em->flush();

        return $responseService->success();
    }

    private static function takePetOutOfDaycare(Pet $pet)
    {
        $hoursInDayCare = (\time() - $pet->getLocationMoveDate()->getTimestamp()) / (60 * 60);

        if($hoursInDayCare >= 4)
        {
            $fourHoursInDayCare = (int)($hoursInDayCare / 4);

            PetExperienceService::spendTimeOnStatusEffects($pet, $fourHoursInDayCare);

            $pet
                ->increasePoison(-$fourHoursInDayCare)
                ->increaseCaffeine(-$fourHoursInDayCare)
                ->increaseAlcohol(-$fourHoursInDayCare)
                ->increasePsychedelic(-$fourHoursInDayCare)
                ->increasePoison(-$fourHoursInDayCare)

                ->increaseFood($fourHoursInDayCare, 12)
                ->increaseSafety($fourHoursInDayCare, 10)
                ->increaseLove($fourHoursInDayCare, 8)
                ->increaseEsteem($fourHoursInDayCare, 6)
            ;
        }

        $pet->setLocation(PetLocationEnum::HOME);
    }
}
