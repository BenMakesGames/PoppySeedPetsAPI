<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\MeritEnum;
use App\Functions\ColorFunctions;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Service\PetColorService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
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
        PetRepository $petRepository, ItemRepository $itemRepository, MeritRepository $meritRepository,
        PetColorService $petColorChangingService, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'iridescentHandCannon');

        $color = strtoupper(trim($request->request->getAlpha('color', '')));

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getTool())
        {
            if($pet->getTool()->isGrayscaling())
                throw new UnprocessableEntityHttpException('It seems the Ambrotypic magic surrounding ' . $pet->getName() . ' is preventing this from working!');

            if($pet->getTool()->isGreenifying())
                throw new UnprocessableEntityHttpException('It seems the magic of ' . $pet->getName() . '\'s 5-leaf Clover is preventing this from working!');
        }

        // make sure the new hue is some minimum distance away from the old hue:
        if($color === 'A')
            $oldColor = $pet->getColorA();
        else
            $oldColor = $pet->getColorB();

        $newColor = $petColorChangingService->randomizeColorDistinctFromPreviousColor($oldColor);

        if($color === 'A')
            $pet->setColorA($newColor);
        else if($color === 'B')
            $pet->setColorB($newColor);
        else
            throw new UnprocessableEntityHttpException('You forgot to choose which color to recolor!');

        if($pet->hasMerit(MeritEnum::HYPERCHROMATIC))
        {
            $responseService->addFlashMessage($pet->getName() . ' has been chromatically altered! (It seems their Hyperchromaticism was blasted away by the cannon, as well!)');
            $pet->removeMerit($meritRepository->findOneByName(MeritEnum::HYPERCHROMATIC));
        }
        else
        {
            $responseService->addFlashMessage($pet->getName() . ' has been chromatically altered!');
        }

        $deleted = $squirrel3->rngNextInt(1, 10) === 1;

        if($deleted)
        {
            $comment = 'This was once an Iridescent Hand Cannon.';

            if($squirrel3->rngNextInt(1, 2) === 1)
            {
                $comment .= ' Then it got rusty and fell apart.';

                if($squirrel3->rngNextInt(1, 2) === 1)
                {
                    $comment .= ' At the same time!';

                    if($squirrel3->rngNextInt(1, 2) === 1)
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
