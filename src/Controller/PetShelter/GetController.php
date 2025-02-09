<?php
declare(strict_types=1);

namespace App\Controller\PetShelter;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Functions\UserQuestRepository;
use App\Repository\PetRepository;
use App\Service\AdoptionService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/petShelter")]
class GetController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAvailablePets(
        AdoptionService $adoptionService, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d');
        /** @var User $user */
        $user = $this->getUser();
        $costToAdopt = $adoptionService->getAdoptionFee($user);
        $lastAdopted = UserQuestRepository::find($em, $user, 'Last Adopted a Pet');

        if($lastAdopted && $lastAdopted->getValue() === $now)
        {
            return $responseService->success([
                'costToAdopt' => $costToAdopt,
                'pets' => [],
                'dialog' => 'To make sure there are enough pets for everyone, we ask that you not adopt more than one pet per day.'
            ]);
        }

        [$pets, $dialog] = $adoptionService->getDailyPets($user);

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
            $dialog .= "no one catches your eye today, come back tomorrow. We get newcomers every day!\n\nSince you have so many pets in your house already, a pet you adopt will be placed into Daycare.";
        else
            $dialog .= "no one catches your eye today, come back tomorrow. We get newcomers every day!";

        $data = [
            'dialog' => $dialog,
            'pets' => $pets,
            'costToAdopt' => $costToAdopt,
            'petsAtHome' => $numberOfPetsAtHome,
            'maxPets' => $user->getMaxPets(),
        ];

        return $responseService->success(
            $data,
            [ SerializationGroupEnum::PET_SHELTER_PET ]
        );
    }
}
