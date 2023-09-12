<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/fieldGuide")
 */
class FieldGuideController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getUnlockedEntries(
        Request $request, ResponseService $responseService
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
            ->mapResults(function($discoveredOn, $comment, $type, $name, $image, $description) {
                return [
                    'discoveredOn' => $discoveredOn,
                    'comment' => $comment,
                    'entry' => [
                        'type' => $type,
                        'name' => $name,
                        'image' => $image,
                        'description' => $description,
                    ]

                ];
            });

        return $responseService->success($entries);
    }

}