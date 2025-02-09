<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Functions\UserMenuFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class SaveMenuOrderController extends AbstractController
{
    #[Route("/menuOrder", methods: ["PATCH"])]
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function saveMenuOrder(
        Request $request, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $newOrder = $request->request->all('order');

        if(count($newOrder) === 0)
            throw new PSPFormValidationException('No order info was provided.');

        UserMenuFunctions::updateUserMenuSortOrder($em, $user, $newOrder);

        $em->flush();

        return $responseService->success();
    }
}
