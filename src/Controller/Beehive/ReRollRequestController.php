<?php
namespace App\Controller\Beehive;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\InventoryRepository;
use App\Service\BeehiveService;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/beehive")
 */
class ReRollRequestController extends AbstractController
{
    /**
     * @Route("/reRoll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function reRollRequest(
        Request $request, ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive) || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $itemId = $request->request->getInt('die', 0);

        if($itemId < 1)
            throw new PSPFormValidationException('A die must be selected!');

        $item = $inventoryRepository->find($itemId);

        if(!$item || $item->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('The selected item does not exist! (Reload and try again?)');

        if(!array_key_exists($item->getItem()->getName(), HollowEarthService::DICE_ITEMS))
            throw new PSPInvalidOperationException('The selected item is not a die!? (Weird! Reload and try again??)');

        $em->remove($item);

        $beehiveService->reRollRequest($user->getBeehive());

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
