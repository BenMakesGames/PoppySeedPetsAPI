<?php

namespace App\Controller\HouseSitting;

use App\Entity\HouseSitter;
use App\Entity\User;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/designatedHouseSitter")]
class RemoveHouseSitterController extends AbstractController
{
    #[Route("", methods: ["DELETE"])]
    public function removeHouseSitter(EntityManagerInterface $em, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $houseSitter = $em->getRepository(HouseSitter::class)->findOneBy(
            [ 'user' => $user->getId() ]
        );

        if($houseSitter == null)
            return $responseService->success();

        $em->remove($houseSitter);
        $em->flush();

        return $responseService->success();
    }
}