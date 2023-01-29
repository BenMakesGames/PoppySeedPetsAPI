<?php
namespace App\Controller\Account;

use App\Repository\MuseumItemRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/account")
 */
class ChangeIconController extends AbstractController
{
    /**
     * @Route("/changeIcon", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setIcon(
        Request $request, MuseumItemRepository $museumItemRepository, ResponseService $responseService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $itemId = $request->request->getInt('item');

        $donated = $museumItemRepository->findOneBy([
            'user' => $user->getId(),
            'item' => $itemId
        ]);

        if(!$donated)
            throw new NotFoundHttpException('You have not donated that item... YET! (I believe in you!)');

        $user->setIcon('items/' . $donated->getItem()->getImage());

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/clearIcon", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function clearIcon(ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $user->setIcon(null);
        $em->flush();

        return $responseService->success();
    }
}
