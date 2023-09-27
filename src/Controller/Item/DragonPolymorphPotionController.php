<?php
namespace App\Controller\Item;

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Repository\DragonRepository;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/dragonPolymorphPotion")
 */
class DragonPolymorphPotionController extends AbstractController
{
    /**
     * @Route("/{inventory}/give", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function drink(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, IRandom $squirrel3,
        DragonRepository $dragonRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'dragonPolymorphPotion/#/give');

        $dragon = $dragonRepository->findOneBy([ 'owner' => $user ]);

        if(!$dragon)
            throw new PSPNotFoundException('You don\'t know any dragons to give the potion to...');

        if(!$dragon->getIsAdult())
            throw new PSPNotFoundException('Your fireplace dragon, ' . $dragon->getName() . ', is too young to drink. Potions.');

        $em->remove($inventory);

        $currentAppearance = $dragon->getAppearance();

        $availableAppearances = array_filter(
            Dragon::APPEARANCE_IMAGES,
            fn(int $image) => $image !== $currentAppearance
        );

        $dragon->setAppearance($squirrel3->rngNextFromArray($availableAppearances));

        $em->flush();

        $responseService->addFlashMessage($dragon->getName() . '\'s physical form has changed!');

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
