<?php
namespace App\Controller\Fireplace;

use App\Entity\Fireplace;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/fireplace")]
class UpdateStockingController extends AbstractController
{
    #[Route("/stocking", methods: ["PATCH"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function saveStockingSettings(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $appearance = $request->request->getAlnum('appearance');
        $colorA = $request->request->getAlnum('colorA');
        $colorB = $request->request->getAlnum('colorB');

        if(!in_array($appearance, Fireplace::STOCKING_APPEARANCES))
            throw new PSPFormValidationException('Must choose a stocking appearance...');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $colorA))
            throw new PSPFormValidationException('Color A is not valid.');

        if(!preg_match('/[A-Fa-f0-9]{6}/', $colorB))
            throw new PSPFormValidationException('Color B is not valid.');

        $user->getFireplace()
            ->setStockingAppearance($appearance)
            ->setStockingColorA($colorA)
            ->setStockingColorB($colorB)
        ;

        $em->flush();

        return $responseService->success();
    }
}
