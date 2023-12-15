<?php

namespace App\Controller\HouseSitting;

use App\Entity\HouseSitter;
use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/designatedHouseSitter")]
class DesignateHouseSitterController extends AbstractController
{
    #[Route("", methods: ["POST"])]
    public function designateHouseSitter(Request $request, EntityManagerInterface $em, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $houseSitterId = $request->request->getInt('houseSitterId');

        if($houseSitterId <= 0)
            throw new PSPFormValidationException('Must select a house sitter.');

        $houseSitterExists = $em->getRepository(User::class)->count([ 'id' => $houseSitterId ]) > 0;

        if(!$houseSitterExists)
            throw new PSPFormValidationException('Must select a house sitter.');

        $houseSitter = $em->getRepository(HouseSitter::class)->findOneBy(
            [ 'user' => $user->getId() ]
        );

        if($houseSitter == null)
        {
            $houseSitter = (new HouseSitter())
                ->setUser($user);

            $em->persist($houseSitter);
        }

        $houseSitter->setHouseSitter($em->getReference(User::class, $houseSitterId));

        $em->flush();

        return $responseService->success();
    }
}