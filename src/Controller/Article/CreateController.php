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


namespace App\Controller\Article;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\Article;
use App\Exceptions\PSPFormValidationException;
use App\Functions\DesignGoalRepository;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/article")]
class CreateController
{
    #[DoesNotRequireHouseHours]
    #[Route("", methods: ["POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function createNew(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        ParameterBagInterface $parameterBag, UserAccessor $userAccessor
    ): JsonResponse
    {
        AdminOnly::adminIPsOnly($parameterBag, $request);

        $title = trim($request->request->getString('title'));
        $body = trim($request->request->getString('body'));
        $imageUrl = trim($request->request->getString('imageUrl'));

        if($title === '' || $body === '')
            throw new PSPFormValidationException('title and body are both required.');

        if(\mb_strlen($title) > 255)
            throw new PSPFormValidationException('title may not be longer than 255 characters.');

        $designGoals = DesignGoalRepository::findByIdsFromParameters($em, $request->request, 'designGoals');

        $article = (new Article())
            ->setImageUrl($imageUrl == '' ? null : $imageUrl)
            ->setTitle($title)
            ->setBody($body)
            ->setAuthor($userAccessor->getUserOrThrow())
        ;

        foreach($designGoals as $designGoal)
            $article->addDesignGoal($designGoal);

        $em->persist($article);
        $em->flush();

        $em->createQuery('UPDATE App\Entity\User u SET u.unreadNews=u.unreadNews+1')->execute();

        return $responseService->success();
    }
}
