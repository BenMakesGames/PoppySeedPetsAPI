<?php

namespace App\Controller\Account;

use App\Entity\User;
use App\Entity\UserLink;
use App\Enum\UserLinkVisibilityEnum;
use App\Enum\UserLinkWebsiteEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class MyLinksController extends AbstractController
{
    #[Route("/my/interwebs", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyInterwebs(ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $myLinks = SimpleDb::createReadOnlyConnection()
            ->query(
                'SELECT id,website,name_or_id AS nameOrId,visibility FROM user_link WHERE user_id = ?',
                [ $user->getId() ]
            )
            ->getResults()
        ;

        return $responseService->success($myLinks);
    }

    #[Route("/my/interwebs/{link}", methods: ["DELETE"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function deleteLink(UserLink $link, ResponseService $responseService, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($link->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('Link not found.');

        $em->remove($link);
        $em->flush();

        return $responseService->success();
    }

    #[Route("/my/interwebs", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function createLink(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $website = trim($request->request->get('website'));
        $nameOrId = trim($request->request->get('nameOrId'));
        $visibility = trim($request->request->get('visibility'));

        if(!UserLinkWebsiteEnum::isAValue($website))
            throw new PSPFormValidationException('Please select a website.');

        if(!UserLinkVisibilityEnum::isAValue($visibility))
            throw new PSPFormValidationException('Please select a visibility.');

        if(strlen($nameOrId) == 0)
            throw new PSPFormValidationException('Please provide a name or ID.');

        if(strlen($nameOrId) > 100)
            throw new PSPFormValidationException('Your name or ID cannot be longer than 100 characters.');

        if(strpos($nameOrId, '/') !== false || strpos($nameOrId, '\\') !== false)
            throw new PSPFormValidationException('Slashes are not allowed.');

        $existingLinks = $em->getRepository(UserLink::class)->count([ 'user' => $user ]);

        if($existingLinks >= 5)
            throw new PSPFormValidationException('You can only have up to 5 links.');

        $link = (new UserLink())
            ->setUser($user)
            ->setWebsite($website)
            ->setNameOrId($nameOrId)
            ->setVisibility($visibility);

        $em->persist($link);
        $em->flush();

        return $responseService->success([
            'id' => $link->getId(),
            'website' => $link->getWebsite(),
            'nameOrId' => $link->getNameOrId(),
            'visibility' => $link->getVisibility(),
        ]);
    }
}