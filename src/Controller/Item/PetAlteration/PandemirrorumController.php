<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
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
 * @Route("/item/pandemirrorum")
 */
class PandemirrorumController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function usePandemirrorum(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, MeritRepository $meritRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'pandemirrorum');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        $invertedMerit = $meritRepository->findOneByName(MeritEnum::INVERTED);
        $veryInvertedMerit = $meritRepository->findOneByName(MeritEnum::VERY_INVERTED);

        if(!$invertedMerit)
            throw new HttpException(500, 'The ' . MeritEnum::INVERTED . ' Merit does not exist! This is a terrible programming error. Someone please tell Ben.');

        if(!$veryInvertedMerit)
            throw new HttpException(500, 'The ' . MeritEnum::VERY_INVERTED . ' Merit does not exist! This is a terrible programming error. Someone please tell Ben.');

        if($pet->hasMerit(MeritEnum::INVERTED))
        {
            $pet->removeMerit($invertedMerit);
            $pet->addMerit($veryInvertedMerit);
            $messageExtra = $pet->getName() . ' has become VERY Inverted!';
        }
        else if($pet->hasMerit(MeritEnum::VERY_INVERTED))
        {
            $pet->removeMerit($veryInvertedMerit);
            $messageExtra = $pet->getName() . ' is no longer inverted at all!';
        }
        else
        {
            $pet->addMerit($invertedMerit);
            $messageExtra = $pet->getName() . ' has become Inverted!';
        }

        $em->remove($inventory);
        $em->flush();

        $responseService->addFlashMessage($pet->getName() . ' stared so hard at the mirror, it shattered! ' . $messageExtra);

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
