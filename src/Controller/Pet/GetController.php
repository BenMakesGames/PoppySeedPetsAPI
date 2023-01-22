<?php
namespace App\Controller\Pet;

use App\Controller\PoppySeedPetsController;
use App\Entity\Pet;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\PetRepository;
use App\Service\Filter\PetFilterService;
use App\Service\ResponseService;
use App\Service\Typeahead\PetTypeaheadService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class GetController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function searchPets(Request $request, ResponseService $responseService, PetFilterService $petFilterService)
    {
        return $responseService->success(
            $petFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_PUBLIC_PROFILE ]
        );
    }

    /**
     * @Route("/my", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyPets(ResponseService $responseService, PetRepository $petRepository)
    {
        $user = $this->getUser();

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        return $responseService->success($petsAtHome, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/my/{id}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyPet(ResponseService $responseService, PetRepository $petRepository, int $id)
    {
        $user = $this->getUser();

        $pet = $petRepository->findOneBy([
            'id' => $id,
            'owner' => $user->getId(),
        ]);

        if(!$pet)
            throw new NotFoundHttpException();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function profile(Pet $pet, ResponseService $responseService)
    {
        return $responseService->success($pet, [ SerializationGroupEnum::PET_PUBLIC_PROFILE ]);
    }

    /**
     * @Route("/typeahead", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, PetTypeaheadService $petTypeaheadService
    )
    {
        $petTypeaheadService->setUser($this->getUser());

        try
        {
            $suggestions = $petTypeaheadService->search('name', $request->query->get('search', ''));

            return $responseService->success($suggestions, [ SerializationGroupEnum::MY_PET, SerializationGroupEnum::MY_PET_LOCATION ]);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }
}
