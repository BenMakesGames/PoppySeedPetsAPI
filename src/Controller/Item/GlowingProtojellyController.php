<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/protojelly")]
class GlowingProtojellyController extends AbstractController
{
    #[Route("/{inventory}/d4", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function d4(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'protojelly/#/d4');

        $die = ItemRepository::findOneByName($em, 'Glowing Four-sided Die');
        $inventory
            ->changeItem($die)
            ->addComment($user->getName() . ' compelled this Glowing Protojelly to take the shape of a tetrahedron.')
        ;

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('The jelly turns into a Glowing Four-sided Die, and becomes solid, never to change its shape again!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/d6", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function d6(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'protojelly/#/d6');

        $die = ItemRepository::findOneByName($em, 'Glowing Six-sided Die');
        $inventory
            ->changeItem($die)
            ->addComment($user->getName() . ' compelled this Glowing Protojelly to take the shape of a cube.')
        ;

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('The jelly turns into a Glowing Six-sided Die, and becomes solid, never to change its shape again!', [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/d8", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function d8(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'protojelly/#/d8');

        $die = ItemRepository::findOneByName($em, 'Glowing Eight-sided Die');
        $inventory
            ->changeItem($die)
            ->addComment($user->getName() . ' compelled this Glowing Protojelly to take the shape of an octahedron.')
        ;

        $responseService->setReloadInventory();

        return $responseService->itemActionSuccess('The jelly turns into a Glowing Eight-sided Die, and becomes solid, never to change its shape again!', [ 'itemDeleted' => true ]);
    }
}