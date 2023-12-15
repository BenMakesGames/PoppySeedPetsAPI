<?php
namespace App\Controller\HouseSitting\HouseSit;

use App\Entity\User;
use App\Functions\HouseSittingHelpers;
use App\Functions\SimpleDb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/houseSit")]
class GetInventoryController extends AbstractController
{
    #[Route("/{houseSitForId}/inventory", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getInventory(int $houseSitForId)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        HouseSittingHelpers::canHouseSitOrThrow($db, $user, $houseSitForId);

        // ...
    }
}