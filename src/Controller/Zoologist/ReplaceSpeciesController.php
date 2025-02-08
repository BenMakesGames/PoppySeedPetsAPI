<?php
declare(strict_types=1);

namespace App\Controller\Zoologist;

use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserSpeciesCollected;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/zoologist")]
class ReplaceSpeciesController extends AbstractController
{
    #[Route("/replaceEntry", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function replace(
        EntityManagerInterface $em, Request $request, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            throw new PSPNotUnlockedException('Zoologist');

        $petId = $request->request->getInt('petId');

        if($petId <= 0)
            throw new PSPFormValidationException('No pets were selected.');

        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $alreadyDiscovered = $em->getRepository(UserSpeciesCollected::class)->findOneBy([
            'user' => $user->getId(),
            'species' => $pet->getSpecies()->getId(),
        ]);

        if(!$alreadyDiscovered)
            throw new PSPFormValidationException('You have not shown a pet of this species to the zoologist yet.');

        if(
            $alreadyDiscovered->getPetName() == $pet->getName() &&
            $alreadyDiscovered->getColorA() == $pet->getPerceivedColorA() &&
            $alreadyDiscovered->getColorB() == $pet->getPerceivedColorB() &&
            $alreadyDiscovered->getScale() == $pet->getScale()
        )
            throw new PSPFormValidationException('This exact pet is already in the zoologist\'s records.');

        $alreadyDiscovered
            ->setPetName($pet->getName())
            ->setColorA($pet->getPerceivedColorA())
            ->setColorB($pet->getPerceivedColorB())
            ->setScale($pet->getScale())
        ;

        $em->flush();

        $responseService->addFlashMessage('"Got it!"');

        return $responseService->success();
    }
}