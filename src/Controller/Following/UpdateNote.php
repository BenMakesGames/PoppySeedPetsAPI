<?php
declare(strict_types=1);

namespace App\Controller\Following;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Entity\UserFollowing;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/following")]
class UpdateNote extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{following}", methods: ["POST"])]
    public function handle(
        User $following, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $followingRecord = $em->getRepository(UserFollowing::class)->findOneBy([
            'user' => $user,
            'following' => $following,
        ]);

        if(!$followingRecord)
            throw new PSPNotFoundException('You\'re not following that person...');

        $note = $request->request->getString('note');

        if($note && \mb_strlen($note) > 255)
            throw new PSPFormValidationException('Note may not be longer than 255 characters. Sorry :(');

        $followingRecord->setNote($note);

        $em->flush();

        return $responseService->success();
    }
}
