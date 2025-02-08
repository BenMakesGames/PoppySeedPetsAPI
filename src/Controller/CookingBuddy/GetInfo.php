<?php
declare(strict_types=1);

namespace App\Controller\CookingBuddy;

use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cookingBuddy')]
class GetInfo extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getInfo(
        EntityManagerInterface $em, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->getCookingBuddy())
            throw new PSPNotFoundException('Cooking Buddy Not Found');

        return $responseService->success([
            'name' => $user->getCookingBuddy()->getName(),
            'appearance' => $user->getCookingBuddy()->getAppearance(),
        ]);
    }
}
