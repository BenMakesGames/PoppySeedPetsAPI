<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/reversable")
 */
class ReversableController extends PsyPetsItemController
{
    private const FLIPS = [
        'Small Plastic Bucket' => 'Upside-down Plastic Bucket'
    ];

    /**
     * @Route("/{inventory}/flip", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function flipPlasticBucket(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'reversable/#/flip');

        $oldItemName = $inventory->getItem()->getName();

        if(array_key_exists($oldItemName, self::FLIPS))
            $newItemName = self::FLIPS[$oldItemName];
        else
        {
            $newItemName = array_search($oldItemName, self::FLIPS);

            if(!$newItemName)
                throw new HttpException(500, $oldItemName . ' cannot be flipped?? This is a result of programmer oversight. Please let Ben know.');
        }

        $newItem = $itemRepository->findOneByName($newItemName);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($newItem)
            ->setModifiedOn()
        ;

        $em->flush();

        $newItemNameArticle = GrammarFunctions::indefiniteArticle($newItemName);

        $message = ArrayFunctions::pick_one([
            'The ' . $oldItemName . ' has been completely transformed, becoming ' . $newItemNameArticle . ' ' . $newItemName . '!' . "\n\n" . 'Incredible.',
            'You rotate the ' . $oldItemName . ' approximately 3.14 radians about its x-axis, et voilà: ' . $newItemNameArticle . ' ' . $newItemName . '!',
            'You deftly flip the ' . $oldItemName . ' into ' . $newItemNameArticle . ' ' . $newItemName . '!',
            'You caaaaaarefully turn the ' . $oldItemName . ' over, then caaaaaarefully put it down...' . "\n\n" . 'Okay... okay, yeah! It worked!' . "\n\n" . 'You successfully made ' . $newItemNameArticle . ' ' . $newItemName . '!',
        ]);

        return $responseService->itemActionSuccess($message, [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => $reloadPets ]);
    }
}
