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
use App\Service\Squirrel3;
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
    public function readBehattingScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, MeritRepository $meritRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'behattingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->hasMerit(MeritEnum::BEHATTED))
            throw new UnprocessableEntityHttpException($pet->getName() . ' already has the Behatted Merit!');

        $merit = $meritRepository->findOneByName(MeritEnum::BEHATTED);

        if(!$merit)
            throw new HttpException(500, 'The ' . MeritEnum::BEHATTED . ' Merit does not exist! This is a terrible programming error. Someone please tell Ben.');

        $em->remove($inventory);

        $pet->addMerit($merit);

        $adjective = $squirrel3->rngNextFromArray([
            'awe-inspiring', 'incredible', 'breathtaking', 'amazing', 'fabulous'
        ]);

        $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was granted the ' . $adjective . ' power to wear hats!', 'items/scroll/behatting');

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
