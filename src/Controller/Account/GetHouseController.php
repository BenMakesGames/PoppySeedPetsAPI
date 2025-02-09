<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/account")]
class GetHouseController extends AbstractController
{
    #[Route("/myHouse", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getHouse(
        ManagerRegistry $doctrine, ResponseService $responseService,
        NormalizerInterface $normalizer
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petRepository = $doctrine->getRepository(Pet::class, 'readonly');
        $inventoryRepository = $doctrine->getRepository(Inventory::class, 'readonly');

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        $inventory = $inventoryRepository->findBy([
            'owner' => $this->getUser(),
            'location' => LocationEnum::HOME
        ]);

        return $responseService->success([
            'inventory' => $normalizer->normalize($inventory, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
            'pets' => $normalizer->normalize($petsAtHome, null, [ 'groups' => [ SerializationGroupEnum::MY_PET ] ])
        ]);
    }
}
