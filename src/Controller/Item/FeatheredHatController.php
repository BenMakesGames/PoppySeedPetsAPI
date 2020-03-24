<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/featheredHat")
 */
class FeatheredHatController extends PoppySeedPetsItemController
{
    private const TWEAKS = [
        'Afternoon Hat' => 'Evening Hat',
    ];

    /**
     * @Route("/{inventory}/tweak", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function tweakHat(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, ItemRepository $itemRepository
    )
    {
        $this->validateInventory($inventory, 'featheredHat/#/tweak');

        $oldItemName = $inventory->getItem()->getName();

        if(array_key_exists($oldItemName, self::TWEAKS))
            $newItemName = self::TWEAKS[$oldItemName];
        else
        {
            $newItemName = array_search($oldItemName, self::TWEAKS);

            if(!$newItemName)
                throw new HttpException(500, $oldItemName . ' cannot be tweaked?? This is a result of programmer oversight. Please let Ben know.');
        }

        $newItem = $itemRepository->findOneByName($newItemName);

        $reloadPets = $inventory->getHolder() || $inventory->getWearer();

        $inventory
            ->changeItem($newItem)
            ->setModifiedOn()
        ;

        $em->flush();

        return $responseService->itemActionSuccess('The hat shifts in color!', [ 'reloadInventory' => true, 'itemDeleted' => true, 'reloadPets' => $reloadPets ]);
    }
}
