<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/password")
 */
class PasswordController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/erase", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function erasePassword(
        Inventory $inventory, ResponseService $responseService, ItemRepository $itemRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'password/#/erase');

        $string = $itemRepository->findOneByName('String');

        $inventory
            ->changeItem($string)
            ->addComment($user->getName() . ' erased the Password that had been stored in this String.')
        ;

        $responseService->setReloadInventory();

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
