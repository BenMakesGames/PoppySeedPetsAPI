<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/twilightFertilizer")
 */
class TwilightFertilizerController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useItem(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'twilightFertilizer/#');

        $user = $this->getUser();

        if(!$user->getUnlockedGreenhouse())
            throw new UnprocessableEntityHttpException('You need a Greenhouse to summon the night to!');

        if($user->getGreenhouse()->getMaxDarkPlants() >= 2)
            throw new UnprocessableEntityHttpException('There\'s nowhere else to put the fertilizer!');

        $user->getGreenhouse()->increaseMaxDarkPlants(1);

        $em->remove($inventory);
        $em->flush();

        if($user->getGreenhouse()->getMaxDarkPlants() === 1)
            return $responseService->itemActionSuccess('You lay down the fertilizer in a dark corner of the Greenhouse. (Is that "summoning the night"? Sure. Why not.)', [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You lay down the fertilizer in a dark corner of the Greenhouse.', [ 'itemDeleted' => true ]);
    }
}
