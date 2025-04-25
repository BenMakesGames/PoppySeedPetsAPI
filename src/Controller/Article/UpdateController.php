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
use App\Controller\AdminController;
use App\Entity\Article;
use App\Entity\DesignGoal;
use App\Exceptions\PSPFormValidationException;
use App\Functions\ArrayFunctions;
use App\Functions\DesignGoalRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/article")]
class UpdateController extends AdminController
{
    #[DoesNotRequireHouseHours]
    #[Route("/{article}", methods: ["POST"], requirements: ["article" => "\d+"])]
    #[IsGranted("ROLE_ADMIN")]
    public function handle(
        Article $article, ResponseService $responseService, Request $request, EntityManagerInterface $em
    ): JsonResponse
    {
        $this->adminIPsOnly($request);

        $title = trim($request->request->getString('title'));
        $body = trim($request->request->getString('body'));
        $imageUrl = trim($request->request->getString('imageUrl'));

        if($title === '' || $body === '')
            throw new PSPFormValidationException('title and body are both required.');

        $designGoals = DesignGoalRepository::findByIdsFromParameters($em, $request->request, 'designGoals');

        $article
            ->setImageUrl($imageUrl == '' ? null : $imageUrl)
            ->setTitle($title)
            ->setBody($body)
        ;

        $currentDesignGoals = $article->getDesignGoals()->toArray();
        $designGoalsToAdd = ArrayFunctions::except($designGoals, $currentDesignGoals, fn(DesignGoal $dg) => $dg->getId());
        $designGoalsToRemove = ArrayFunctions::except($currentDesignGoals, $designGoals, fn(DesignGoal $dg) => $dg->getId());

        foreach($designGoalsToRemove as $toRemove)
            $article->removeDesignGoal($toRemove);

        foreach($designGoalsToAdd as $toAdd)
            $article->addDesignGoal($toAdd);

        $em->flush();

        return $responseService->success();
    }
}
