<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\PetRepository;
use App\Service\Filter\PetFilterService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class DaycareController extends AbstractController
{
    /**
     * @Route("/daycare", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyDaycarePets(
        ResponseService $responseService, PetFilterService $petFilterService, Request $request
    )
    {
        $user = $this->getUser();

        $petFilterService->addRequiredFilter('owner', $user->getId());
        $petFilterService->addRequiredFilter('location', PetLocationEnum::DAYCARE);

        $petsInDaycare = $petFilterService->getResults($request->query);

        return $responseService->success(
            $petsInDaycare,
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_PET ]
        );
    }

    /**
     * @Route("/{pet}/putInDaycare", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function putPetInDaycare(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('This isn\'t your pet.');

        if(!$pet->isAtHome())
            throw new UnprocessableEntityHttpException($pet->getName() . ' isn\'t at home...');

        $pet->setLocation(PetLocationEnum::DAYCARE);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/takeOutOfDaycare", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takePetOutOfDaycare(
        Pet $pet, ResponseService $responseService, PetRepository $petRepository, EntityManagerInterface $em,
        PetExperienceService $petExperienceService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('This isn\'t your pet.');

        if($pet->getLocation() !== PetLocationEnum::DAYCARE)
            throw new UnprocessableEntityHttpException($pet->getName() . ' isn\'t in Daycare...');

        $petsAtHome = $petRepository->getNumberAtHome($user);

        if($petsAtHome >= $user->getMaxPets())
            throw new UnprocessableEntityHttpException('Your house has too many pets as-is.');

        $hoursInDayCare = (\time() - $pet->getLocationMoveDate()->getTimestamp()) / (60 * 60);

        if($hoursInDayCare >= 4)
        {
            $fourHoursInDayCare = (int)($hoursInDayCare / 4);

            $petExperienceService->spendTimeOnStatusEffects($pet, $fourHoursInDayCare);

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

        $em->flush();

        return $responseService->success();
    }
}
