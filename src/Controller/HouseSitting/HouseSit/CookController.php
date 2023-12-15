<?php
namespace App\Controller\HouseSitting\HouseSit;

use App\Entity\User;
use App\Functions\HouseSittingHelpers;
use App\Functions\SimpleDb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/houseSit")]
class CookController extends AbstractController
{
    #[Route("/{houseSitForId}/inventory/cook", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function cook(int $houseSitForId, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        HouseSittingHelpers::canHouseSitOrThrow($db, $user, $houseSitForId);

        // ...
    }
}