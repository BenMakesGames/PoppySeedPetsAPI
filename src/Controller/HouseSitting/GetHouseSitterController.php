<?php

namespace App\Controller\HouseSitting;

use App\Entity\User;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/designatedHouseSitter")]
class GetHouseSitterController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    public function getHouseSitter(ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $db = SimpleDb::createReadOnlyConnection();

        $houseSitters = $db
            ->query(
                '
                    SELECT id,name,icon
                    FROM user
                        LEFT JOIN house_sitter ON house_sitter.house_sitter_id = user.id
                    WHERE house_sitter.user_id = ? LIMIT 1
                ',
                [ $user->getId() ]
            )
            ->getResults();

        if(count($houseSitters) == 0)
            return $responseService->success([ 'houseSitter' => null ]);

        return $responseService->success([ 'houseSitter' => $houseSitters[0] ]);
    }
}