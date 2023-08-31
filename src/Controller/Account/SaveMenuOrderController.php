<?php
namespace App\Controller\Account;

use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Service\ResponseService;
use App\Service\UserMenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/account")
 */
class SaveMenuOrderController extends AbstractController
{
    /**
     * @Route("/menuOrder", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @DoesNotRequireHouseHours()
     */
    public function saveMenuOrder(
        Request $request, UserMenuService $userMenuService, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $newOrder = $request->request->get('order');

        if(!is_array($newOrder) || count($newOrder) === 0)
            throw new PSPFormValidationException('No order info was provided.');

        $userMenuService->updateUserMenuSortOrder($user, $newOrder);

        $em->flush();

        return $responseService->success();
    }
}
