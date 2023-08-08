<?php
namespace App\Controller\Item\Scroll;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotEnoughCurrencyException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetRenamingHelpers;
use App\Functions\ProfanityFilterFunctions;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/renamingScroll")
 */
class RenamingController extends AbstractController
{
    /**
     * @Route("/{inventory}/read", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function readRenamingScroll(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'renamingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        PetRenamingHelpers::renamePet($responseService, $pet, $request->request->get('name', ''));

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/readToSpiritCompanion", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function renameSpiritCompanion(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'renamingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->getSpiritCompanion())
            throw new PSPNotFoundException('That pet does not have a spirit companion.');

        PetRenamingHelpers::renameSpiritCompanion($responseService, $pet->getSpiritCompanion(), $request->request->get('name', ''));

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/readToSelf", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function renameYourself(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        TransactionService $transactionService, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $pointsRemaining = $user->getMuseumPoints() - $user->getMuseumPointsSpent();

        if($pointsRemaining < 500)
            throw new PSPNotEnoughCurrencyException('500 Favor', $pointsRemaining);

        ItemControllerHelpers::validateInventory($user, $inventory, 'renamingScroll');

        $newName = ProfanityFilterFunctions::filter(trim($request->request->get('name', '')));

        if($newName === $user->getName())
            throw new PSPInvalidOperationException('That\'s already your name! (What a waste of the scroll that would be...)');

        if(\mb_strlen($newName) < 2 || \mb_strlen($newName) > 30)
            throw new PSPFormValidationException('Name must be between 2 and 30 characters long.');

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $oldName = $user->getName();

        $user->setName($newName);

        $transactionService->spendMuseumFavor($user, 500, 'You renamed yourself, from ' . $oldName . ' to ' . $newName . '!');

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
