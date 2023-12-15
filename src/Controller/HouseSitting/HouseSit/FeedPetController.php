<?php
namespace App\Controller\HouseSitting\HouseSit;

use App\Entity\Pet;
use App\Entity\User;
use App\Functions\HouseSittingHelpers;
use App\Functions\SimpleDb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/houseSit")]
class FeedPetController extends AbstractController
{
    #[Route("/{houseSitForId}/pets/{pet}/feed", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function feedPet(int $houseSitForId, Pet $pet, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        HouseSittingHelpers::canHouseSitOrThrow($db, $user, $houseSitForId);

        // ...
    }
}