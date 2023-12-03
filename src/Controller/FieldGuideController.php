<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\SimpleDb;
use App\Service\CommentFormatter;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/fieldGuide")]
class FieldGuideController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getUnlockedEntries(
        ResponseService $responseService, CommentFormatter $commentFormatter
    )
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