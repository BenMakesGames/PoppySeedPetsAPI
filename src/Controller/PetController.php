<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Enum\SerializationGroup;
use App\Repository\InventoryRepository;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class PetController extends PsyPetsController
{
    /**
     * @Route("/my", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyPets(ResponseService $responseService)
    {
        return $responseService->success($this->getUser()->getPets(), SerializationGroup::MY_PETS);
    }

    /**
     * @Route("/{pet}/pet", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, PetService $petService
    )
    {
        try
        {
            $activity = $petService->doPet($pet);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success(
            [ 'pet' => $pet, 'activity' => [ $activity ] ],
            [ SerializationGroup::MY_PETS, SerializationGroup::PET_ACTIVITY_LOGS ]
        );
    }

    /**
     * @Route("/{pet}/feed", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feed(
        Pet $pet, Request $request, InventoryRepository $inventoryRepository, ResponseService $responseService,
        PetService $petService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $items = $request->request->get('items');

        if(!\is_array($items)) $items = [ $items ];

        $inventory = $inventoryRepository->findBy([
            'user' => $user,
            'id' => $items
        ]);

        if(\count($items) !== \count($inventory))
            throw new UnprocessableEntityHttpException('At least one of the items selected doesn\'t seem to exist??');

        try
        {
            $activity = $petService->doFeed($pet, $inventory);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success(
            [ 'pet' => $pet, 'activity' => [ $activity ] ],
            [ SerializationGroup::MY_PETS, SerializationGroup::PET_ACTIVITY_LOGS ]
        );
    }
}