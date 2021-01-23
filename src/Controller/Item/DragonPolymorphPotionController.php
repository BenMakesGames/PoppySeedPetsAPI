<?php
namespace App\Controller\Item;

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\DragonRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/dragonPolymorphPotion")
 */
class DragonPolymorphPotionController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/give", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function drink(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        DragonRepository $dragonRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'dragonPolymorphPotion/#/give');

        $dragon = $dragonRepository->findOneBy([ 'owner' => $user ]);

        if(!$dragon)
            throw new NotFoundHttpException('You don\'t know any dragons to give the potion to...');

        if(!$dragon->getIsAdult())
            throw new NotFoundHttpException('Your fireplace dragon, ' . $dragon->getName() . ', is too young to drink. Potions.');

        $em->remove($inventory);

        $currentAppearance = $dragon->getAppearance();

        $availableAppearances = array_filter(
            Dragon::APPEARANCE_IMAGES,
            function(int $image) use($currentAppearance) {
                return $image !== $currentAppearance;
            }
        );

        $dragon->setAppearance($squirrel3->rngNextFromArray($availableAppearances));

        $em->flush();

        $responseService->addFlashMessage($dragon->getName() . '\'s physical form has changed!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
