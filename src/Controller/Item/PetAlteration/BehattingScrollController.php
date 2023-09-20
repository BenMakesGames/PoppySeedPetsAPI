<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/behattingScroll")
 */
class BehattingScrollController extends AbstractController
{
    /**
     * @Route("/{inventory}/read", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readBehattingScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, MeritRepository $meritRepository, Squirrel3 $squirrel3,
        UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'behattingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::BEHATTED))
            throw new PSPInvalidOperationException($pet->getName() . ' already has the Behatted Merit!');

        $merit = $meritRepository->deprecatedFindOneByName(MeritEnum::BEHATTED);

        if(!$merit)
            throw new \Exception('The ' . MeritEnum::BEHATTED . ' Merit does not exist! This is a terrible programming error. Someone please tell Ben.');

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

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
