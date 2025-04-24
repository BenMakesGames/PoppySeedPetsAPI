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


namespace App\Controller;

use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\SimpleDb;
use App\Service\CommentFormatter;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/fieldGuide")]
class FieldGuideController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getUnlockedEntries(
        ResponseService $responseService, CommentFormatter $commentFormatter
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::FieldGuide))
            throw new PSPNotUnlockedException('Field Guide');

        $entries = SimpleDb::createReadOnlyConnection()
            ->query(
                'SELECT ue.discovered_on,ue.comment,e.type,e.name,e.image,e.description
                FROM user_field_guide_entry AS ue
                INNER JOIN field_guide_entry AS e ON e.id = ue.entry_id
                WHERE ue.user_id = ?
                ORDER BY e.name ASC',
                [ $user->getId() ]
            )
            ->mapResults(fn($discoveredOn, $comment, $type, $name, $image, $description) =>
                [
                    'discoveredOn' => $discoveredOn,
                    'comment' => $commentFormatter->format($comment),
                    'entry' => [
                        'type' => $type,
                        'name' => $name,
                        'image' => $image,
                        'description' => $description,
                    ]
                ]
            );

        return $responseService->success($entries);
    }

}