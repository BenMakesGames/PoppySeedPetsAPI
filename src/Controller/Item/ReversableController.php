<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/reversable")
 */
class ReversableController extends AbstractController
{
    private const FLIPS = [
        'Small Plastic Bucket' => 'Upside-down Plastic Bucket',
        'Shiny Pail' => 'Upside-down Shiny Pail',
        'Small, Yellow Plastic Bucket' => 'Upside-down, Yellow Plastic Bucket',
        'Saucepan' => 'Upside-down Saucepan',
        'Silver Colander' => 'Upside-down Silver Colander',
        'Pie Crust' => 'Upside-down Pie Crust',
    ];

    /**
     * @Route("/{inventory}/flip", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function flipIt(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'reversable/#/flip');

        $oldItemName = $inventory->getItem()->getName();

        if(array_key_exists($oldItemName, self::FLIPS))
            $newItemName = self::FLIPS[$oldItemName];
        else
        {
            $newItemName = array_search($oldItemName, self::FLIPS);

            if(!$newItemName)
                throw new \Exception($oldItemName . ' cannot be flipped?? This is a result of programmer oversight. Please let Ben know.');
        }

        $newItem = ItemRepository::findOneByName($em, $newItemName);

        $inventory
            ->changeItem($newItem)
            ->setModifiedOn()
        ;

        $em->flush();

        $message = $squirrel3->rngNextFromArray([
            'The ' . $oldItemName . ' has been completely transformed, becoming ' . $newItem->getNameWithArticle() . '!' . "\n\n" . 'Incredible.',
            'You rotate the ' . $oldItemName . ' approximately 3.14 radians about its x-axis, et voilà: ' . $newItem->getNameWithArticle() . '!',
            'You deftly flip the ' . $oldItemName . ' into ' . $newItem->getNameWithArticle() . '!',
            'You caaaaaarefully turn the ' . $oldItemName . ' over, then caaaaaarefully put it down...' . "\n\n" . 'Okay... okay, yeah! It worked!' . "\n\n" . 'You successfully made ' . $newItem->getNameWithArticle() . '!',
            'You confidently toss the ' . $oldItemName . ' into the air with a twist, close your eyes, turn around, and catch it behind you.' . "\n\n" . 'A bit ostentatious, but effective nonetheless: you now have ' . $newItem->getNameWithArticle() . '!'
        ]);

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
