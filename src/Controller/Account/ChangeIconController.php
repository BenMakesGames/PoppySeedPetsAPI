<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Entity\MuseumItem;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class ChangeIconController extends AbstractController
{
    #[Route("/changeIcon", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function setIcon(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $itemId = $request->request->getInt('item');

        $donated = $em->getRepository(MuseumItem::class)->findOneBy([
            'user' => $user->getId(),
            'item' => $itemId
        ]);

        if(!$donated)
            throw new PSPNotFoundException('You have not donated that item... YET! (I believe in you!)');

        $user->setIcon('items/' . $donated->getItem()->getImage());

        $em->flush();

        return $responseService->success();
    }

    #[Route("/clearIcon", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function clearIcon(ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $user->setIcon(null);
        $em->flush();

        return $responseService->success();
    }
}
