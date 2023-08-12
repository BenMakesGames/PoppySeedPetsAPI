<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\PetColorFunctions;
use App\Repository\DragonRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/spicyKonpeito")
 */
class SpicyKonpeitoController extends AbstractController
{
    /**
     * @Route("/{inventory}/give", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedToDragon(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        DragonRepository $dragonRepository, PetColorFunctions $petColorChangingService,
        Squirrel3 $rng
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'spicyKonpeito/#/give');

        $dragon = $dragonRepository->findOneBy([ 'owner' => $user ]);

        if(!$dragon || !$dragon->getIsAdult())
            throw new PSPNotUnlockedException('Dragon Den');

        if(!$inventory->getSpice() || $inventory->getSpice()->getEffects()->getSpicy() === 0)
            throw new PSPInvalidOperationException($dragon->getName() . ' is excited at first, but takes a sniff, and realizes the Konpeitō hasn\'t been properly spiced! (That Konpeitō\'s gotta\'s be SPICY!)');

        $em->remove($inventory);

        $newColorA = $petColorChangingService->randomizeColorDistinctFromPreviousColor($rng, $dragon->getColorA());
        $newColorB = $petColorChangingService->randomizeColorDistinctFromPreviousColor($rng, $dragon->getColorB());

        $dragon
            ->setColorA($newColorA)
            ->setColorB($newColorB)
        ;

        $em->flush();

        $responseService->addFlashMessage($dragon->getName() . '\'s colors have been altered!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
