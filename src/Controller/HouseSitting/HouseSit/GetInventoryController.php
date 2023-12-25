<?php
namespace App\Controller\HouseSitting\HouseSit;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\HouseSittingHelpers;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/houseSit")]
class GetInventoryController extends AbstractController
{
    #[Route("/{houseSitForId}/inventory", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getInventory(int $houseSitForId, ManagerRegistry $doctrine, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        HouseSittingHelpers::canHouseSitOrThrow($db, $user, $houseSitForId);

        $inventory = $doctrine->getRepository(Inventory::class, 'readonly')->findBy([
            'owner' => $houseSitForId,
            'location' => LocationEnum::HOME
        ]);

        return $responseService->success($inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}