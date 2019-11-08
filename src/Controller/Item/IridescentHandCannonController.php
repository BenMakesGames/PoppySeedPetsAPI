<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/iridescentHandCannon")
 */
class IridescentHandCannonController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/fire", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function fireHandCannon(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'iridescentHandCannon');

        $color = strtoupper(trim($request->request->getAlpha('color', '')));

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        // make sure the new hue is some minimum distance away from the old hue:
        if($color === 'A')
            $oldColor = $pet->getColorA();
        else
            $oldColor = $pet->getColorB();

        $oldRGB = ColorFunctions::Hex2RGB($oldColor);
        $oldHSL = ColorFunctions::RGB2HSL($oldRGB['r'], $oldRGB['g'], $oldRGB['b']);

        $h = $oldHSL['h'] + mt_rand(200, 800) / 1000.0;
        if($h > 1) $h -= 1;

        // now pick a random saturation and luminosity within that:
        $s = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
        $l = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

        $newColor = ColorFunctions::HSL2Hex($h, $s, $l);

        if($color === 'A')
            $pet->setColorA($newColor);
        else if($color === 'B')
            $pet->setColorB($newColor);
        else
            throw new UnprocessableEntityHttpException('You forgot to choose which color to recolor!');

        $deleted = mt_rand(1, 10) === 1;

        if($deleted)
        {
            $comment = 'This was once an Iridescent Hand Cannon.';

            if(mt_rand(1, 2) === 1)
            {
                $comment .= ' Then it got rusty and fell apart.';

                if(mt_rand(1, 2) === 1)
                {
                    $comment .= ' At the same time!';

                    if(mt_rand(1, 2) === 1)
                        $comment .= ' (It\'s more common than you\'d think!)';
                }
            }

            $inventory
                ->changeItem($itemRepository->findOneByName('Rusty Blunderbuss'))
                ->addComment($comment)
                ->setModifiedOn()
            ;
        }

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => $deleted ]);
    }
}
