<?php
namespace App\Controller\Following;

use App\Entity\User;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\UserFollowingRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/following")]
class UpdateNote extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{following}", methods={"POST"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function handle(
        User $following, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserFollowingRepository $userFollowingRepository
    )
    {
        $user = $this->getUser();

        $followingRecord = $userFollowingRepository->findOneBy([
            'user' => $user,
            'following' => $following,
        ]);

        if(!$followingRecord)
            throw new PSPNotFoundException('You\'re not following that person...');

        $note = $request->request->get('note');

        if($note && \mb_strlen($note) > 255)
            throw new PSPFormValidationException('Note may not be longer than 255 characters. Sorry :(');

        $followingRecord->setNote($note);

        $em->flush();

        return $responseService->success();
    }
}
