<?php
namespace App\Controller\Account;

use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Repository\PassphraseResetRequestRepository;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use App\Repository\UserStyleRepository;
use App\Service\PassphraseResetService;
use App\Service\ResponseService;
use App\Service\UserMenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/updateEmail", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updateEmail(
        Request $request, ResponseService $responseService, UserPasswordHasherInterface $passwordEncoder,
        UserRepository $userRepository
    )
    {
        $user = $this->getUser();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newEmail = trim($request->request->get('newEmail'));

        if($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('Email address is not valid.');

        $alreadyInUse = $userRepository->findOneBy([ 'email' => $newEmail ]);

        if($alreadyInUse)
        {
            if($alreadyInUse->getId() === $user->getId())
                return $responseService->success(); // sure.
            else
            {
                if(strpos($newEmail, '+') === -1)
                {
                    $emailParts = explode('@', $newEmail);
                    $exampleEmail = $emailParts[0] . '+whatever@' . $emailParts[1];
                    throw new UnprocessableEntityHttpException('That e-mail address is already in use. If it\'s your e-mail address, many e-mail services allow you to put a "+" in your address, for example "' . $exampleEmail . '".');
                }
                else
                    throw new UnprocessableEntityHttpException('That e-mail address is already in use.');
            }
        }

        $user->setEmail($newEmail);

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/updatePassphrase", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updatePassphrase(
        Request $request, ResponseService $responseService, UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $newPassphrase = trim($request->request->get('newPassphrase'));

        if(\mb_strlen($newPassphrase) < 10)
            throw new UnprocessableEntityHttpException('Passphrase must be at least 10 characters long.');

        $user->setPassword($passwordEncoder->hashPassword($user, $newPassphrase));

        $em->flush();

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAccount(
        ResponseService $responseService, EntityManagerInterface $em, UserStyleRepository $userStyleRepository
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedMuseum() === null && $user->getRegisteredOn() <= (new \DateTimeImmutable())->modify('-3 days'))
        {
            $user->setUnlockedMuseum();
            $em->flush();
        }

        return $responseService->success(
            [ 'currentTheme' => $userStyleRepository->findCurrent($user) ],
            [ SerializationGroupEnum::MY_STYLE ]
        );
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/requestPassphraseReset", methods={"POST"})
     */
    public function requestPassphraseReset(
        Request $request, UserRepository $userRepository, ResponseService $responseService,
        PassphraseResetService $passphraseResetService
    )
    {
        $email = trim($request->request->get('email', ''));

        if($email === '')
            throw new UnprocessableEntityHttpException('E-mail address is required.');

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new UnprocessableEntityHttpException('E-mail address is invalid.');

        $user = $userRepository->findOneBy([ 'email' => $email ]);

        if(!$user)
            throw new UnprocessableEntityHttpException('There is no user with that e-mail address.');

        $passphraseResetService->requestReset($user);

        return $responseService->success();
    }

    /**
     * @DoesNotRequireHouseHours()
     * @Route("/requestPassphraseReset/{code}", methods={"POST"})
     */
    public function resetPassphrase(
        string $code, Request $request, PassphraseResetRequestRepository $passwordResetRequestRepository,
        UserPasswordHasherInterface $userPasswordEncoder, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $passphrase = trim($request->request->get('passphrase', ''));

        if(\mb_strlen($passphrase) < 10)
            throw new UnprocessableEntityHttpException('Passphrase must be at least 10 characters long. (Pro tip: try using an actual phrase, or short sentence!)');

        $resetRequest = $passwordResetRequestRepository->findOneBy([ 'code' => $code ]);

        if(!$resetRequest || $resetRequest->getExpiresOn() <= new \DateTimeImmutable())
            throw new NotFoundHttpException('This reset URL is invalid, or expired.');

        $user = $resetRequest->getUser();

        $user->setPassword($userPasswordEncoder->hashPassword($user, $passphrase));

        $em->remove($resetRequest);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/stats", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getStats(ResponseService $responseService, UserStatsRepository $userStatsRepository)
    {
        $stats = $userStatsRepository->findBy([ 'user' => $this->getUser() ]);

        return $responseService->success($stats, [ SerializationGroupEnum::MY_STATS ]);
    }

    /**
     * @Route("/myHouse", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getHouse(
        PetRepository $petRepository, InventoryRepository $inventoryRepository, ResponseService $responseService,
        NormalizerInterface $normalizer
    )
    {
        $user = $this->getUser();

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'location' => PetLocationEnum::HOME
        ]);

        $inventory = $inventoryRepository->findBy([
            'owner' => $this->getUser(),
            'location' => LocationEnum::HOME
        ]);

        return $responseService->success([
            'inventory' => $normalizer->normalize($inventory, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
            'pets' => $normalizer->normalize($petsAtHome, null, [ 'groups' => [ SerializationGroupEnum::MY_PET ] ])
        ]);
    }

    /**
     * @Route("/menuOrder", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function saveMenuOrder(
        Request $request, UserMenuService $userMenuService, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $newOrder = $request->request->get('order');

        $userMenuService->updateUserMenuSortOrder($this->getUser(), $newOrder);

        $em->flush();

        return $responseService->success();
    }
}
