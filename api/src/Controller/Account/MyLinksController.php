<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Controller\Account;

use App\Entity\UserLink;
use App\Enum\UserLinkVisibilityEnum;
use App\Enum\UserLinkWebsiteEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

class MyLinksController
{
    #[Route("/my/interwebs", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMyInterwebs(ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

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
    public function deleteLink(
        UserLink $link, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($link->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('Link not found.');

        $em->remove($link);
        $em->flush();

        return $responseService->success();
    }

    #[Route("/my/interwebs", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function createLink(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $websiteName = mb_trim($request->request->getString('website'));
        $nameOrId = mb_trim($request->request->getString('nameOrId'));
        $visibilityName = mb_trim($request->request->getString('visibility'));

        $website = UserLinkWebsiteEnum::tryFrom($websiteName)
            ?? throw new PSPFormValidationException('Please select a website.');

        $visibility = UserLinkVisibilityEnum::tryFrom($visibilityName)
            ?? throw new PSPFormValidationException('Please select a visibility.');

        if(strlen($nameOrId) == 0)
            throw new PSPFormValidationException('Please provide a name or ID.');

        if(strlen($nameOrId) > 100)
            throw new PSPFormValidationException('Your name or ID cannot be longer than 100 characters.');

        if(str_contains($nameOrId, '/') || str_contains($nameOrId, '\\'))
            throw new PSPFormValidationException('Slashes are not allowed.');

        $existingLinks = $em->getRepository(UserLink::class)->count([ 'user' => $user ]);

        if($existingLinks >= 5)
            throw new PSPFormValidationException('You can only have up to 5 links.');

        $link = new UserLink(
            user: $user,
            website: $website,
            nameOrId: $nameOrId,
            visibility: $visibility,
        );

        $em->persist($link);
        $em->flush();

        return $responseService->success([
            'id' => $link->getId(),
            'website' => $link->getWebsite()->value,
            'nameOrId' => $link->getNameOrId(),
            'visibility' => $link->getVisibility()->value,
        ]);
    }
}