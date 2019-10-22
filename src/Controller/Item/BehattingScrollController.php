<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\MeritEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/behattingScroll")
 */
class BehattingScrollController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function invokeFruitScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, MeritRepository $meritRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'behattingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($pet->hasMerit(MeritEnum::BEHATTED))
            throw new UnprocessableEntityHttpException($pet->getName() . ' already has the Behatted Merit!');

        $merit = $meritRepository->findOneByName(MeritEnum::BEHATTED);

        if(!$merit)
            throw new HttpException(500, 'The ' . MeritEnum::BEHATTED . ' Merit does not exist! This is a terrible programming error. Someone please tell Ben.');

        $em->remove($inventory);

        $pet->addMerit($merit);

        $em->flush();

        return $responseService->itemActionSuccess([ 'itemDeleted' => true ]);
    }
}
