<?php
namespace App\Controller\Item;

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\DragonRepository;
use App\Service\PetColorChangingService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/spicyKonpeito")
 */
class SpicyKonpeitoController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/give", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedToDragon(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        DragonRepository $dragonRepository, PetColorChangingService $petColorChangingService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'spicyKonpeito/#/give');

        $dragon = $dragonRepository->findOneBy([ 'owner' => $user ]);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t know any dragons to give the ' . $inventory->getItem()->getName() . ' to...');

        if(!$dragon->getIsAdult())
            throw new NotFoundHttpException('Your fireplace dragon, ' . $dragon->getName() . ', is too young for such an adult snack!');

        if(!$inventory->getSpice() || $inventory->getSpice()->getEffects()->getSpicy() === 0)
            throw new NotFoundHttpException($dragon->getName() . ' is excited at first, but takes a sniff, and realizes the Konpeitō hasn\'t been properly spiced! (That Konpeitō\'s gotta\'s be SPICY!)');

        $em->remove($inventory);

        $newColorA = $petColorChangingService->RandomizeColorDistinctFromPreviousColor($dragon->getColorA());
        $newColorB = $petColorChangingService->RandomizeColorDistinctFromPreviousColor($dragon->getColorB());

        $dragon
            ->setColorA($newColorA)
            ->setColorB($newColorB)
        ;

        $em->flush();

        $responseService->addFlashMessage($dragon->getName() . '\'s colors have been altered!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
