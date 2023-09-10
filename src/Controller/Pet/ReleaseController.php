<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\PetLocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\EquipmentFunctions;
use App\Model\PetChanges;
use App\Model\PetShelterPet;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class ReleaseController extends AbstractController
{
    /**
     * @Route("/{pet}/release", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function releasePet(
        Pet $pet, Request $request, ResponseService $responseService, UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $em, UserRepository $userRepository, PetRepository $petRepository, Squirrel3 $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getLocation() !== PetLocationEnum::DAYCARE && $pet->getLocation() !== PetLocationEnum::HOME)
            throw new PSPInvalidOperationException('Only pets at home, or in the daycare, may be released to the wilds.');

        $petCount = $petRepository->getTotalOwned($user);

        if($petCount === 1)
            throw new PSPInvalidOperationException('You can\'t release your very last pet! That would be FOOLISH!');

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $state = new PetChanges($pet);

        EquipmentFunctions::unequipPet($pet);
        EquipmentFunctions::unhatPet($pet);

        $pet
            ->setName($rng->rngNextFromArray(PetShelterPet::PET_NAMES)) // to prevent people from releasing rude names for other players to pick up
            ->setOwner($userRepository->findOneByEmail('the-wilds@poppyseedpets.com'))
            ->setParkEventType(null)
            ->setNote('')
            ->setCostume('')
            ->setLocation(PetLocationEnum::DAYCARE)
            ->increaseEsteem(-5 * ($pet->getLevel() + 1))
            ->increaseSafety(-5 * ($pet->getLevel() + 1))
            ->increaseLove(-6 * ($pet->getLevel() + 1))
            ->setLastInteracted(new \DateTimeImmutable())
        ;

        if($user->getHollowEarthPlayer() && $user->getHollowEarthPlayer()->getChosenPet())
        {
            if($user->getHollowEarthPlayer()->getChosenPet()->getId() === $pet->getId())
                $user->getHollowEarthPlayer()->setChosenPet(null);
        }

        $activityLog = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($user->getName() . ' gave up ' . ActivityHelpers::PetName($pet) . ', releasing them to The Wilds.')
            ->setChanges($state->compare($pet))
        ;

        $em->persist($activityLog);

        $em->flush();

        return $responseService->success();
    }
}
