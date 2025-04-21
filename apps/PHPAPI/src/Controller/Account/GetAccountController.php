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

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Functions\UserStyleFunctions;
use App\Service\PerformanceProfiler;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class GetAccountController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getAccount(
        ResponseService $responseService, EntityManagerInterface $em,
        PerformanceProfiler $performanceProfiler
    )
    {
        $time = microtime(true);

        /** @var User $user */
        $user = $this->getUser();

        $currentTheme = UserStyleFunctions::findCurrent($em, $user->getId());

        $response = $responseService->success(
            [ 'currentTheme' => $currentTheme ],
            [ SerializationGroupEnum::MY_STYLE ]
        );

        $performanceProfiler->logExecutionTime(__METHOD__, microtime(true) - $time);

        return $response;
    }
}
