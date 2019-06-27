<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Enum\SerializationGroup;
use App\Repository\InventoryRepository;
use App\Repository\PetActivityLogRepository;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
        return $responseService->success($this->getUser()->getPets(), SerializationGroup::MY_PET);
    }

    /**
     * @Route("/{pet}", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function profile(
        Pet $pet, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $groups = [
            SerializationGroup::PUBLIC_PROFILE
        ];

        if($user !== null && $user->getId() === $pet->getOwner()->getId())
        {
            $groups[] = SerializationGroup::MY_PET;
        }

        return $responseService->success($pet, $groups);
    }

    /**
     * @Route("/{pet}/logs", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function logs(
        Pet $pet, ResponseService $responseService, PetActivityLogRepository $petActivityLogRepository
    )
    {
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new AccessDeniedHttpException();

        $logs = $petActivityLogRepository->findBy([ 'pet' => $pet->getId() ], [ 'id' => 'DESC' ], 10);

        return $responseService->success($logs, SerializationGroup::PET_ACTIVITY_LOGS);
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
            $petService->doPet($pet);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success(
            $pet,
            SerializationGroup::MY_PET
        );
    }

    /**
     * @Route("/{pet}/praise", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function praise(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, PetService $petService
    )
    {
        try
        {
            $petService->doPraise($pet);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success(
            $pet,
            SerializationGroup::MY_PET
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
            'owner' => $user,
            'id' => $items
        ]);

        if(\count($items) !== \count($inventory))
            throw new UnprocessableEntityHttpException('At least one of the items selected doesn\'t seem to exist??');

        try
        {
            $petService->doFeed($pet, $inventory);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success(
            $pet,
            SerializationGroup::MY_PET
        );
    }
}