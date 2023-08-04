<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/twilightFertilizer")
 */
class TwilightFertilizerController extends AbstractController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'twilightFertilizer/#');

        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Greenhouse))
            throw new PSPNotUnlockedException('Greenhouse');

        if($user->getGreenhouse()->getMaxDarkPlants() >= 2)
            throw new PSPInvalidOperationException('There\'s nowhere else to put the fertilizer!');

        $user->getGreenhouse()->increaseMaxDarkPlants(1);

        $em->remove($inventory);
        $em->flush();

        if($user->getGreenhouse()->getMaxDarkPlants() === 1)
            return $responseService->itemActionSuccess('You lay down the fertilizer in a dark corner of the Greenhouse. (Is that "summoning the night"? Sure. Why not.)', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You lay down the fertilizer in a dark corner of the Greenhouse.', [ 'itemDeleted' => true ]);
    }
}
