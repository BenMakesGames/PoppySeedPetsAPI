<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Merit;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\SpiritCompanion;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\PetActivityLogRepository;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use App\Service\Filter\DaycareFilterService;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\MeritService;
use App\Service\PetService;
use App\Service\ResponseService;
use App\Service\Typeahead\PetTypeaheadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class PetController extends PoppySeedPetsController
{
    /**
     * @Route("/my", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyPets(ResponseService $responseService, PetRepository $petRepository)
    {
        $user = $this->getUser();

        $petsAtHome = $petRepository->findBy([
            'owner' => $user->getId(),
            'inDaycare' => false,
        ]);

        return $responseService->success($petsAtHome, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/daycare", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyDaycarePets(
        ResponseService $responseService, DaycareFilterService $daycareFilterService, Request $request
    )
    {
        $user = $this->getUser();

        $daycareFilterService->addRequiredFilter('user', $user->getId());

        $petsInDaycare = $daycareFilterService->getResults($request->query);

        return $responseService->success(
            $petsInDaycare,
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_PET ]
        );
    }

    /**
     * @Route("/{pet}", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function profile(Pet $pet, ResponseService $responseService)
    {
        return $responseService->success($pet, SerializationGroupEnum::PET_PUBLIC_PROFILE);
    }

    /**
     * @Route("/{pet}/release", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function releasePet(
        Pet $pet, Request $request, ResponseService $responseService, UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em, UserRepository $userRepository, PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('This isn\'t your pet.');

        $petCount = $petRepository->getTotalOwned($user);

        if($petCount === 1)
            throw new UnprocessableEntityHttpException('You can\'t release your very last pet! That would be FOOLISH!');

        if(!$passwordEncoder->isPasswordValid($user, $request->request->get('confirmPassphrase')))
            throw new AccessDeniedHttpException('Passphrase is not correct.');

        $state = new PetChanges($pet);

        $pet
            ->setTool(null)
            ->setHat(null)
            ->setOwner($userRepository->findOneByEmail('the-wilds@poppyseedpets.com'))
            ->setParkEventType(null)
            ->setNote('')
            ->setCostume('')
            ->setInDaycare(true)
            ->increaseEsteem(-5 * ($pet->getLevel() + 1))
            ->increaseSafety(-5 * ($pet->getLevel() + 1))
            ->increaseLove(-6 * ($pet->getLevel() + 1))
        ;

        if($user->getHollowEarthPlayer()->getChosenPet() !== null && $user->getHollowEarthPlayer()->getChosenPet()->getId() === $pet->getId())
            $user->getHollowEarthPlayer()->setChosenPet(null);

        $activityLog = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($user->getName() . ' gave up ' . $pet->getName() . ', releasing them to The Wilds.')
            ->setChanges($state->compare($pet))
        ;

        $em->persist($activityLog);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/putInDaycare", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function putPetInDaycare(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('This isn\'t your pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is already in Daycare.');

        $pet
            ->setParkEventType(null) // unregister from park events
            ->setInDaycare(true)
        ;

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/takeOutOfDaycare", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takePetOutOfDaycare(
        Pet $pet, ResponseService $responseService, PetRepository $petRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('This isn\'t your pet.');

        if(!$pet->getInDaycare())
            throw new UnprocessableEntityHttpException($pet->getName() . ' isn\'t in Daycare...');

        $petsAtHome = $petRepository->getNumberAtHome($user);

        if($petsAtHome >= $user->getMaxPets())
            throw new UnprocessableEntityHttpException('Your house has too many pets as-is.');

        $pet->setInDaycare(false);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/friends", methods={"GET"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getPetFriends(Pet $pet, ResponseService $responseService)
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('This isn\'t your pet.');

        return $responseService->success($pet->getPetRelationships(), SerializationGroupEnum::PET_FRIEND);
    }

    /**
     * @Route("/{pet}/updateNote", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updateNote(
        Pet $pet, Request $request, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        $note = trim($request->request->get('note', ''));

        if(strlen($note) > 1000)
            throw new UnprocessableEntityHttpException('Note cannot be longer than 1000 characters.');

        $pet->setNote($note);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/equip/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function equipPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        if($inventory->getHolder())
        {
            $inventory->getHolder()->setTool(null);
            $em->flush();
        }

        if($inventory->getWearer())
        {
            $inventory->getWearer()->setHat(null);
            $em->flush();
        }

        $pet->setTool($inventory);
        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/hat/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function hatPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        if(!$pet->hasMerit(MeritEnum::BEHATTED))
            throw new UnprocessableEntityHttpException($pet->getName() . ' does not have the Merit required to wear hats.');

        if($inventory->getHolder())
        {
            $inventory->getHolder()->setTool(null);
            $em->flush();
        }

        if($inventory->getWearer())
        {
            $inventory->getWearer()->setHat(null);
            $em->flush();
        }

        $pet->setHat($inventory);
        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/availableMerits", methods={"GET"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailableMerits(Pet $pet, ResponseService $responseService, MeritService $meritService)
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        return $responseService->success($meritService->getAvailableMerits($pet), SerializationGroupEnum::AVAILABLE_MERITS);
    }

    /**
     * @Route("/{pet}/unequip", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unequipPet(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        if(!$pet->getTool())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is not currently equipped.');

        $pet->setTool(null);

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/unhat", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unhatPet(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        if(!$pet->getHat())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is not currently wearing a hat.');

        $pet->setHat(null);

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/setFertility", methods={"PATCH"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setPetFertility(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if(!$pet->hasMerit(MeritEnum::VOLAGAMY))
            throw new AccessDeniedHttpException($pet->getName() . ' does not have the ' . MeritEnum::VOLAGAMY . ' Merit.');

        $fertility = $request->request->getBoolean('fertility');

        $pet->setIsFertile($fertility);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/costume", methods={"PATCH"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function setCostume(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        $costume = trim($request->request->get('costume'));

        if(strlen($costume) > 30)
            throw new UnprocessableEntityHttpException('Costume description cannot be longer than 30 characters.');

        $pet->setCostume($costume);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/chooseAffectionReward", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function chooseAffectionReward(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, MeritService $meritService
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if($pet->getAffectionRewardsClaimed() >= $pet->getAffectionLevel())
            throw new UnprocessableEntityHttpException('You\'ll have to raise ' . $pet->getName() . '\'s affection, first.');

        $meritName = $request->request->get('merit');

        $availableMerits = $meritService->getAvailableMerits($pet);

        /** @var Merit $merit */
        $merit = ArrayFunctions::find_one($availableMerits, function(Merit $m) use($meritName) {
            return $m->getName() === $meritName;
        });

        if(!$merit)
            throw new UnprocessableEntityHttpException('That merit is not available.');

        $pet
            ->addMerit($merit)
            ->increaseAffectionRewardsClaimed()
        ;

        if($merit->getName() === MeritEnum::SPIRIT_COMPANION)
        {
            $spiritCompanion = new SpiritCompanion();

            $pet->setSpiritCompanion($spiritCompanion);

            $em->persist($spiritCompanion);
        }
        else if($merit->getName() === MeritEnum::VOLAGAMY)
        {
            $pet->setIsFertile(true);
        }

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/logs", methods={"GET"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function logs(
        Pet $pet, ResponseService $responseService, PetActivityLogsFilterService $petActivityLogsFilterService,
        Request $request
    )
    {
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new AccessDeniedHttpException();

        $petActivityLogsFilterService->addDefaultFilter('pet', $pet->getId());

        return $responseService->success(
            $petActivityLogsFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ACTIVITY_LOGS ]
        );
    }

    /**
     * @Route("/{pet}/pet", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, PetService $petService
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('You can\'t pet that pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        try
        {
            $petService->doPet($pet);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/praise", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function praise(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, PetService $petService
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('You can\'t praise that pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        try
        {
            $petService->doPraise($pet);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success($pet, SerializationGroupEnum::MY_PET);
    }

    /**
     * @Route("/{pet}/feed", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feed(
        Pet $pet, Request $request, InventoryRepository $inventoryRepository, ResponseService $responseService,
        PetService $petService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('You can\'t feed that pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        $items = $request->request->get('items');

        if(!\is_array($items)) $items = [ $items ];

        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'id' => $items,
            'location' => LocationEnum::HOME,
        ]);

        if(\count($items) !== \count($inventory))
            throw new UnprocessableEntityHttpException('At least one of the items selected doesn\'t seem to exist??');

        try
        {
            $petService->doFeed($pet, $inventory);
        }
        catch(\Exception $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success(
            $pet,
            SerializationGroupEnum::MY_PET
        );
    }

    /**
     * @Route("/typeahead", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function typeaheadSearch(
        Request $request, ResponseService $responseService, PetTypeaheadService $petTypeaheadService
    )
    {
        $petTypeaheadService->setUser($this->getUser());

        try
        {
            $suggestions = $petTypeaheadService->search('name', $request->query->get('search', ''));

            return $responseService->success($suggestions, SerializationGroupEnum::MY_PET);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }
}