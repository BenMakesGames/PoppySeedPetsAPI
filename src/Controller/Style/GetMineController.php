<?php
namespace App\Controller\Style;

use App\Entity\User;
use App\Entity\UserStyle;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/style")]
class GetMineController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getThemes(EntityManagerInterface $em, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();
        $themes = $em->getRepository(UserStyle::class)->findBy([ 'user' => $user ]);

        return $responseService->success($themes, [ SerializationGroupEnum::MY_STYLE ]);
    }
}
