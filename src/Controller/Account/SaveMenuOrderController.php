<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Functions\UserMenuFunctions;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/account")]
class SaveMenuOrderController extends AbstractController
{
    /**
     * @Route("/menuOrder", methods={"PATCH"})
     * @DoesNotRequireHouseHours()
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function saveMenuOrder(
        Request $request, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $newOrder = $request->request->get('order');

        if(!is_array($newOrder) || count($newOrder) === 0)
            throw new PSPFormValidationException('No order info was provided.');

        UserMenuFunctions::updateUserMenuSortOrder($em, $user, $newOrder);

        $em->flush();

        return $responseService->success();
    }
}
