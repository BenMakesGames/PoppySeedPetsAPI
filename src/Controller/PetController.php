<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Repository\PetActivityLogRepository;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\PetService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        return $responseService->success($this->getUser()->getPets(), SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function profile(
        Pet $pet, ResponseService $responseService
    )
    {
        return $responseService->success($pet, SerializationGroupEnum::PET_PUBLIC_PROFILE);
    }

    /**
     * @Route("/{pet}/updateNote", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updateNote(
        Pet $pet, Request $request, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        $note = trim($request->request->get('note', ''));

        if(strlen($note) > 1000)
            return new UnprocessableEntityHttpException('Note cannot be longer than 1000 characters.');

        $pet->setNote($note);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/equip/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function equipPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($inventory->getPet())
        {
            $inventory->getPet()->setTool(null);
            $em->flush();
        }

        $pet->setTool($inventory);
        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/unequip", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unequipPet(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if(!$pet->getTool())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is not currently equipped.');

        $pet->setTool(null);

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/logs", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function logs(
        Pet $pet, ResponseService $responseService, PetActivityLogsFilterService $petActivityLogsFilterService,
        Request $request
    )
    {
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new AccessDeniedHttpException();

        $petActivityLogsFilterService->addDefaultFilter('pet', $pet->getId());

        return $responseService->success(
            $petActivityLogsFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ACTIVITY_LOGS ]
        );
    }

    /**
     * @Route("/{pet}/pet", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, PetService $petService
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('You can\'t pet that pet.');

        try
        {
            $petService->doPet($pet);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/praise", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function praise(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, PetService $petService
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('You can\'t praise that pet.');

        try
        {
            $petService->doPraise($pet);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
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
            SerializationGroupEnum::MY_PET
        );
    }
}