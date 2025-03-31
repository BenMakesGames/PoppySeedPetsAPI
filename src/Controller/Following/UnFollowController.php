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


namespace App\Controller\Following;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Entity\UserFollowing;
use App\Exceptions\PSPNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/following")]
class UnFollowController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{following}", methods: ["DELETE"])]
    public function handle(
        User $following, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $followingRecord = $em->getRepository(UserFollowing::class)->findOneBy([
            'user' => $user,
            'following' => $following,
        ]);

        if(!$followingRecord)
            throw new PSPNotFoundException('You\'re not following that person...');

        $em->remove($followingRecord);
        $em->flush();

        return $responseService->success();
    }
}
