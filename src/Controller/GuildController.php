<?php
namespace App\Controller;

use App\Entity\Guild;
use App\Enum\SerializationGroupEnum;
use App\Repository\GuildRepository;
use App\Service\Filter\PetFilterService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/guild")
 */
class GuildController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function getAll(ResponseService $responseService, GuildRepository $guildRepository)
    {
        return $responseService->success($guildRepository->findAll(), SerializationGroupEnum::GUILD_ENCYCLOPEDIA);
    }

    /**
     * @Route("/{guild}", methods={"GET"})
     */
    public function getGuild(
        Guild $guild, ResponseService $responseService, PetFilterService $petFilterService, Request $request
    )
    {
        $petFilterService->addRequiredFilter('guild', $guild->getId());

        $members = $petFilterService->getResults($request->query);

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
