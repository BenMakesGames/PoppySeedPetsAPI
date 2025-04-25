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


namespace App\Controller\Guilds;

use App\Entity\Guild;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\GuildMemberFilterService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/guild")]
class GetMembersController
{
    #[Route("/{guild}", methods: ["GET"])]
    public function getGuild(
        Guild $guild, ResponseService $responseService, GuildMemberFilterService $guildMemberFilterService, Request $request
    ): JsonResponse
    {
        $guildMemberFilterService->addRequiredFilter('guild', $guild->getId());

        $members = $guildMemberFilterService->getResults($request->query);

        return $responseService->success(
            [
                'guild' => $guild,
                'members' => $members
            ],
            [
                SerializationGroupEnum::FILTER_RESULTS,
                SerializationGroupEnum::GUILD_MEMBER,
                SerializationGroupEnum::GUILD_ENCYCLOPEDIA,
            ]
        );
    }
}
