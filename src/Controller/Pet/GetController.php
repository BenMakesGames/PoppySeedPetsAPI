<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Service\Filter\PetFilterService;
use App\Service\ResponseService;
use App\Service\Typeahead\PetTypeaheadService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/pet")]
class GetController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    public function searchPets(Request $request, ResponseService $responseService, PetFilterService $petFilterService)
    {
        return $responseService->success(
            $petFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_PUBLIC_PROFILE ]
        );
    }

    #[Route("/my", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyPets(ResponseService $responseService, ManagerRegistry $doctrine)
    {
        /** @var User $user */
        $user = $this->getUser();

        $petRepository = $doctrine->getRepository(Pet::class, 'readonly');

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        return $responseService->success($petsAtHome, [ SerializationGroupEnum::MY_PET ]);
    }

    #[Route("/my/{id}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyPet(ResponseService $responseService, PetRepository $petRepository, int $id)
    {
        /** @var User $user */
        $user = $this->getUser();

        $pet = $petRepository->findOneBy([
            'id' => $id,
            'owner' => $user->getId(),
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function profile(Pet $pet, ResponseService $responseService)
    {
        return $responseService->success($pet, [ SerializationGroupEnum::PET_PUBLIC_PROFILE ]);
    }

    #[Route("/typeahead", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, PetTypeaheadService $petTypeaheadService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petTypeaheadService->setUser($user);

        if($request->query->has('speciesId'))
            $petTypeaheadService->setSpeciesId($request->query->getInt('speciesId'));

        $suggestions = $petTypeaheadService->search('name', $request->query->get('search', ''));

        return $responseService->success($suggestions, [ SerializationGroupEnum::MY_PET, SerializationGroupEnum::MY_PET_LOCATION ]);
    }
}
