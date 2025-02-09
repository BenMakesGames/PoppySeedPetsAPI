<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/password")]
class PasswordController extends AbstractController
{
    #[Route("/{inventory}/erase", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function erasePassword(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'password/#/erase');

        $string = ItemRepository::findOneByName($em, 'String');

        $inventory
            ->changeItem($string)
            ->addComment($user->getName() . ' erased the Password that had been stored in this String.')
        ;

        $responseService->setReloadInventory();

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
