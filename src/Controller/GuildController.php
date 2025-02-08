<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Guild;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\GuildMemberFilterService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/guild")]
class GuildController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    public function getAll(ResponseService $responseService, EntityManagerInterface $em)
    {
        return $responseService->success($em->getRepository(Guild::class)->findAll(), [ SerializationGroupEnum::GUILD_ENCYCLOPEDIA ]);
    }

    #[Route("/{guild}", methods: ["GET"])]
    public function getGuild(
        Guild $guild, ResponseService $responseService, GuildMemberFilterService $guildMemberFilterService, Request $request
    )
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
