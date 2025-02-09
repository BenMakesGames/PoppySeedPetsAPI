<?php
declare(strict_types=1);

namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\PetBadgeEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\ProfanityFilterFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class CostumeController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/costume", methods: ["PATCH"], requirements: ["pet" => "\d+"])]
    public function setCostume(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $costume = trim($request->request->get('costume'));

        if(\mb_strlen($costume) > 30)
            throw new PSPFormValidationException('Costume description cannot be longer than 30 characters.');

        $costume = ProfanityFilterFunctions::filter($costume);

        if(\mb_strlen($costume) > 30)
            $costume = \mb_substr($costume, 0, 30);

        $pet->setCostume($costume);

        PetBadgeHelpers::awardBadgeAndLog($em, $pet, PetBadgeEnum::WAS_GIVEN_A_COSTUME_NAME, ActivityHelpers::UserName($user) . ' gave ' . ActivityHelpers::PetName($pet) . '\'s Halloween costume a name.');

        $em->flush();

        return $responseService->success();
    }
}
