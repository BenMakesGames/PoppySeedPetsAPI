<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/recoveredArchive")
 */
class RecoveredArchiveController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/open", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function openRecoveredArchive(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'recoveredArchive/#/open');

        $user = $this->getUser();

        if($inventoryService->countInventory($user, '3D Printer', $inventory->getLocation()) < 1)
        {
            return $responseService->itemActionSuccess('You peek at the archive\'s contents. It appears to be a model file for a 3D Printer! If only you had a 3D Printer, you might be able to use it to print the object!');
        }

        $loot = ArrayFunctions::pick_one([
            'Bananananers Foster Recipe',
            'Bass Guitar',
            'Big Book of Baking',
            'Bird Bath Blueprint',
            'Blue Balloon',
            'Blue Plastic Egg',
            'Bulbun Plushy',
            'Canned Food',
            'Captain\'s Log',
            'Chocolate Bar',
            'Cobbler Recipe',
            'Compass',
            'Compass (the Math Kind)',
            'Cool Sunglasses',
            'Dumbbell',
            'Egg Book Audiobook',
            'EP',
            'Feathers',
            'Fiberglass Flute',
            'Fish Bag',
            'Flowerbomb',
            'Fluff',
            'Flute',
            'Fried Egg',
            'Glowing Six-sided Die',
            'Glue',
            'Gold Bar',
            'Graham Cracker',
            'Grappling Hook',
            'Green Dye',
            'Green Scissors',
            'Hot Dog',
            'Hourglass',
            'Iron Bar',
            'L-Square',
            'Limestone',
            'Linens and Things',
            'Mango',
            'Maraca',
            'Mirror',
            'Minor Scroll of Riches',
            'Noodles',
            'Orange Chocolate Bar',
            'Painted Dumbbell',
            'Paper',
            'Pie Crust',
            'Pie Recipes',
            'Piece of Cetgueli\'s Map',
            'Plastic',
            'Poker',
            'Puddin\' Rec\'pes',
            'Purple Balloon',
            'Red',
            'Red Balloon',
            'Rib',
            'Rock',
            'Ruler',
            'Scroll of Flowers',
            'Scroll of Fruit',
            'Scythe',
            'Secret Seashell',
            'Silver Bar',
            'Simple Sushi',
            'Single',
            'Smashed Potatoes',
            'SOUP',
            'Spicy Chocolate Bar',
            'Spider',
            'Spirit Polymorph Potion Recipe',
            'Stereotypical Bone',
            'Stereotypical Torch',
            'Straw Broom',
            'String',
            'Sun Flag',
            'Sweet Roll',
            'Tentacle',
            'The Art of Tofu',
            'The Umbra',
            'Tinfoil Hat',
            'Toy Alien Gun',
            'Upside-down Saucepan',
            'Useless Fizz',
            'Welcome Note',
            'White Cloth',
            'White Flag',
            'Yellow Balloon',
            'Yellow Dye',
        ]);

        $item = $itemRepository->findOneByName($loot);

        $message = "You peek at the archive's contents. It appears to be a model file for a 3D Printer!\n\nYou load the archive into your 3D Printer. Without any Plastic whatsoever, the device springs to life and begins printing at a furious rate! After the sparks stop and smoke clears, you see that it printed " . $item->getNameWithArticle() . "!\n\n";

        $message .= ArrayFunctions::pick_one([
            'Weird.',
            'Okay, then...',
            'Is that how that\'s supposed to happen?',
        ]);

        $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' printed this in their 3D Printer, using ' . $inventory->getItem()->getNameWithArticle() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }
}
