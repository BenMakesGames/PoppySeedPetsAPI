<?php
namespace App\Controller;

use App\Entity\Guild;
use App\Entity\Inventory;
use App\Entity\LunchboxItem;
use App\Entity\Merit;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetRelationship;
use App\Entity\SpiritCompanion;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\RelationshipEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\GuildRepository;
use App\Repository\InventoryRepository;
use App\Repository\MeritRepository;
use App\Repository\PetActivityLogRepository;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserRepository;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\Filter\PetFilterService;
use App\Service\Filter\PetRelationshipFilterService;
use App\Service\InventoryService;
use App\Service\MeritService;
use App\Service\PetActivityStatsService;
use App\Service\PetRelationshipService;
use App\Service\PetService;
use App\Service\ProfanityFilterService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\Typeahead\PetRelationshipTypeaheadService;
use App\Service\Typeahead\PetTypeaheadService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/pet")
 */
class PetController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function searchPets(Request $request, ResponseService $responseService, PetFilterService $petFilterService)
    {
        return $responseService->success(
            $petFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_PUBLIC_PROFILE ]
        );
    }

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

        return $responseService->success($petsAtHome, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/daycare", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMyDaycarePets(
        ResponseService $responseService, PetFilterService $petFilterService, Request $request
    )
    {
        $user = $this->getUser();

        $petFilterService->addRequiredFilter('owner', $user->getId());
        $petFilterService->addRequiredFilter('inDaycare', 1);

        $petsInDaycare = $petFilterService->getResults($request->query);

        return $responseService->success(
            $petsInDaycare,
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_PET ]
        );
    }

    /**
     * @Route("/search", methods={"GET"})
     */
    public function search(PetFilterService $petFilterService, Request $request, ResponseService $responseService)
    {
        $results = $petFilterService->getResults($request->query);

        return $responseService->success($results, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::PET_PUBLIC_PROFILE
        ]);
    }

    /**
     * @Route("/{pet}", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function profile(Pet $pet, ResponseService $responseService)
    {
        return $responseService->success($pet, [ SerializationGroupEnum::PET_PUBLIC_PROFILE ]);
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
            ->setLastInteracted(new \DateTimeImmutable())
        ;

        if($user->getHollowEarthPlayer() && $user->getHollowEarthPlayer()->getChosenPet())
        {
            if($user->getHollowEarthPlayer()->getChosenPet()->getId() === $pet->getId())
                $user->getHollowEarthPlayer()->setChosenPet(null);
        }

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
     * @Route("/{pet}/relationships", methods={"GET"}, requirements={"pet"="\d+"})
     */
    public function getPetRelationships(
        Pet $pet, ResponseService $responseService, Request $request,
        PetRelationshipFilterService $petRelationshipFilterService
    )
    {
        $petRelationshipFilterService->addRequiredFilter('pet', $pet);

        $relationships = $petRelationshipFilterService->getResults($request->query);

        return $responseService->success($relationships, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::PET_FRIEND
        ]);
    }

    /**
     * @Route("/{pet}/friends", methods={"GET"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getPetFriends(
        Pet $pet, ResponseService $responseService, NormalizerInterface $normalizer,
        PetRelationshipRepository $petRelationshipRepository
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('This isn\'t your pet.');

        $relationships = $petRelationshipRepository->getFriends($pet);

        return $responseService->success([
            'spiritCompanion' => $normalizer->normalize($pet->getSpiritCompanion(), null, [ 'groups' => [ SerializationGroupEnum::MY_PET ]]),
            'groups' => $normalizer->normalize($pet->getGroups(), null, [ 'groups' => [ SerializationGroupEnum::PET_GROUP ]]),
            'relationshipCount' => $petRelationshipRepository->countRelationships($pet),
            'friends' => $normalizer->normalize($relationships, null, [ 'groups' => [ SerializationGroupEnum::PET_FRIEND ]]),
            'guild' => $normalizer->normalize($pet->getGuildMembership(), null, [ 'groups' => [ SerializationGroupEnum::PET_GUILD ]])
        ]);
    }

    /**
     * @Route("/{pet}/selfReflection", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getGuildMembership(
        Pet $pet, ResponseService $responseService, GuildRepository $guildRepository,
        PetRelationshipRepository $petRelationshipRepository, PetRelationshipService $petRelationshipService
    )
    {
        // just to prevent scraping (this endpoint is currently - 2020-06-29 - used only for changing a pet's guild)
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('That pet doesn\'t belong to you.');

        $guildData = array_map(
            function(Guild $g) {
                return [
                    'id' => $g->getId(),
                    'name' => $g->getName(),
                ];
            },
            $guildRepository->findAll()
        );

        $numberDisliked = $petRelationshipRepository->countRelationships($pet, [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ]);

        if($numberDisliked <= 5)
        {
            $relationships = $petRelationshipRepository->findBy([
                'pet' => $pet,
                'currentRelationship' => [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ],
            ], [], $numberDisliked);

            $troubledRelationships = array_map(
                function(PetRelationship $r) use($petRelationshipService)
                {
                    $possibleRelationships = $petRelationshipService->getRelationshipsBetween(
                        $petRelationshipService->max(RelationshipEnum::FRIEND, $r->getRelationshipGoal()),
                        $petRelationshipService->max(RelationshipEnum::FRIEND, $r->getRelationship()->getRelationshipWith($r->getPet())->getRelationshipGoal())
                    );

                    return [
                        'pet' => $r->getRelationship(),
                        'possibleRelationships' => $possibleRelationships
                    ];
                },
                $relationships
            );
        }
        else
            $troubledRelationships = null;

        $data = [
            'troubledRelationships' => $troubledRelationships,
            'troubledRelationshipsCount' => $numberDisliked,
            'membership' => $pet->getGuildMembership(),
            'guilds' => $guildData,
        ];

        return $responseService->success($data, [ SerializationGroupEnum::PET_GUILD, SerializationGroupEnum::PET_PUBLIC_PROFILE ]);
    }

    /**
     * @Route("/{pet}/familyTree", methods={"GET"})
     */
    public function getFamilyTree(Pet $pet, ResponseService $responseService, PetRepository $petRepository)
    {
        $siblings = $petRepository->findSiblings($pet);
        $parents = $petRepository->findParents($pet);

        $grandparents = [];
        foreach($parents as $parent)
            $grandparents = array_merge($grandparents, $petRepository->findParents($parent));

        $children = $petRepository->findChildren($pet);

        return $responseService->success([
            'grandparents' => $grandparents,
            'parents' => $parents,
            'siblings' => $siblings,
            'children' => $children,
        ], [ SerializationGroupEnum::PET_FRIEND ]);
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
            throw new NotFoundHttpException('There is no such pet.');

        $note = trim($request->request->get('note', ''));

        if(\mb_strlen($note) > 1000)
            throw new UnprocessableEntityHttpException('Note cannot be longer than 1000 characters.');

        $pet->setNote($note);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/putInLunchbox/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function putFoodInLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$inventory->getItem()->getFood())
            throw new UnprocessableEntityHttpException('Only foods can be placed into lunchboxes.');

        if(count($pet->getLunchboxItems()) >= 4)
            throw new UnprocessableEntityHttpException('A lunchbox cannot contain more than 4 items.');

        if($inventory->getHolder())
            throw new UnprocessableEntityHttpException($inventory->getHolder()->getName() . ' is currently holding that item!');

        if($inventory->getWearer())
            throw new UnprocessableEntityHttpException($inventory->getWearer()->getName() . ' is currently wearing that item!');

        if($inventory->getLunchboxItem())
            throw new UnprocessableEntityHttpException('That item is in ' . $inventory->getLunchboxItem()->getPet()->getName() . '\'s lunchbox!');

        $inventory
            ->setLocation(LocationEnum::LUNCHBOX)
            ->setSellPrice(null)
        ;

        $lunchboxItem = (new LunchboxItem())
            ->setPet($pet)
            ->setInventoryItem($inventory)
        ;

        $em->persist($lunchboxItem);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/takeOutOfLunchbox/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function takeFoodOutOfLunchbox(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if(!$inventory->getLunchboxItem())
            throw new UnprocessableEntityHttpException('That item is not in a lunchbox! (Reload and try again?)');

        $inventory
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;

        $em->remove($inventory->getLunchboxItem());

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/equip/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function equipPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if(!$inventory->getItem()->getTool())
            throw new UnprocessableEntityHttpException('That item\'s not an equipment!');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        if(
            $pet->hasStatusEffect(StatusEffectEnum::WEREFORM) &&
            $inventory->getItem()->getTreasure() &&
            $inventory->getItem()->getTreasure()->getSilver() > 0
        )
        {
            throw new UnprocessableEntityHttpException($pet->getName() . ' recoils at the sight of the silvery ' . $inventory->getFullItemName() . '!');
        }

        if($pet->getTool())
        {
            if($inventory->getId() === $pet->getTool()->getId())
                throw new UnprocessableEntityHttpException($pet->getName() . ' is already equipped with that ' . $pet->getTool()->getFullItemName() . '!');

            $inventoryService->unequipPet($pet);
        }

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

        // equip the tool
        $pet->setTool($inventory);

        // move it to the wardrobe
        $inventory
            ->setLocation(LocationEnum::WARDROBE)
            ->setSellPrice(null)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/hat/{inventory}", methods={"POST"}, requirements={"pet"="\d+", "inventory"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function hatPet(
        Pet $pet, Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if($inventory->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That item does not exist.');

        if(!$inventory->getItem()->getHat())
            throw new UnprocessableEntityHttpException('That item\'s not a hat!');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        if(!$pet->hasMerit(MeritEnum::BEHATTED))
            throw new UnprocessableEntityHttpException($pet->getName() . ' does not have the Merit required to wear hats.');

        if(
            $pet->hasStatusEffect(StatusEffectEnum::WEREFORM) &&
            $inventory->getItem()->getTreasure() &&
            $inventory->getItem()->getTreasure()->getSilver() > 0
        )
        {
            throw new UnprocessableEntityHttpException($pet->getName() . ' recoils at the sight of the silvery ' . $inventory->getFullItemName() . '!');
        }

        if($pet->getHat())
        {
            if($inventory->getId() === $pet->getHat()->getId())
                throw new UnprocessableEntityHttpException($pet->getName() . ' is already wearing that ' . $pet->getHat()->getFullItemName() . '!');

            $inventoryService->unhatPet($pet);
        }

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

        // equip the hat
        $pet->setHat($inventory);

        // move it to the wardrobe
        $inventory
            ->setLocation(LocationEnum::WARDROBE)
            ->setSellPrice(null)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
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

        return $responseService->success($meritService->getAvailableMerits($pet), [ SerializationGroupEnum::AVAILABLE_MERITS ]);
    }

    /**
     * @Route("/{pet}/unequip", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function unequipPet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,  InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if($pet->getInDaycare())
            throw new UnprocessableEntityHttpException('Pets in daycare cannot be interacted with.');

        if(!$pet->getTool())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is not currently equipped.');

        $inventoryService->unequipPet($pet);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
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

        $pet->getHat()
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;

        $pet->setHat(null);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
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
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        ProfanityFilterService $profanityFilterService
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        $costume = trim($request->request->get('costume'));

        if(\mb_strlen($costume) > 30)
            throw new UnprocessableEntityHttpException('Costume description cannot be longer than 30 characters.');

        $costume = $profanityFilterService->filter($costume);

        if(\mb_strlen($costume) > 30)
            $costume = \mb_substr($costume, 0, 30);

        $pet->setCostume($costume);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/{pet}/chooseAffectionReward/merit", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function chooseAffectionRewardMerit(
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
        $merit = ArrayFunctions::find_one($availableMerits, fn(Merit $m) => $m->getName() === $meritName);

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

        $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% took the "' . $merit->getName() . '" Merit at %user:' . $user->getId() . '.name\'s% suggestion.', 'ui/merit-icon');

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/chooseAffectionReward/skill", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function chooseAffectionRewardSkill(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException($pet->getName() . ' is not your pet.');

        if($pet->getAffectionRewardsClaimed() >= $pet->getAffectionLevel())
            throw new UnprocessableEntityHttpException('You\'ll have to raise ' . $pet->getName() . '\'s affection, first.');

        $skillName = $request->request->get('skill');

        if(!PetSkillEnum::isAValue($skillName))
            throw new UnprocessableEntityHttpException('"' . $skillName . '" is not a skill!');

        if($pet->getSkills()->{'get' . $skillName}() >= 20)
            throw new UnprocessableEntityHttpException($pet->getName() . '\'s ' . $skillName . ' is already max!');

        $pet->getSkills()->increaseStat($skillName);
        $pet->increaseAffectionRewardsClaimed();

        $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% trained hard in ' . $skillName . ' at %user:' . $user->getId() . '.name\'s% suggestion.', 'ui/merit-icon');

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/selfReflection/changeGuild", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function changeGuild(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        GuildRepository $guildRepository
    )
    {
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new AccessDeniedHttpException();

        if($pet->getSelfReflectionPoint() < 1)
            throw new UnprocessableEntityHttpException($pet->getName() . ' does not have any Self-reflection Points remaining.');

        if(!$pet->getGuildMembership())
            throw new UnprocessableEntityHttpException($pet->getName() . 'isn\'t in a guild!');

        $guildId = $request->request->getInt('guildId');

        if(!$guildId)
            throw new UnprocessableEntityHttpException('You gotta\' choose a guild!');

        $guild = $guildRepository->find($guildId);

        if(!$guild)
            throw new NotFoundHttpException();

        if($pet->getGuildMembership()->getGuild()->getId() === $guild->getId())
            throw new UnprocessableEntityHttpException($pet->getName() . ' is already a member of ' . $guild->getName() . '...');

        $oldGuildName = $pet->getGuildMembership()->getGuild()->getName();

        $pet->getGuildMembership()
            ->setGuild($guild)
        ;

        $pet->increaseSelfReflectionPoint(-1);

        $responseService->createActivityLog($pet, $pet->getName() . ' left ' . $oldGuildName . ', and joined ' . $guild->getName() . '!', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/selfReflection/reconcile", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function reconcileWithAnotherPet(
        Pet $pet, Request $request, ResponseService $responseService, PetRelationshipRepository $petRelationshipRepository,
        EntityManagerInterface $em, PetRelationshipService $petRelationshipService, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new AccessDeniedHttpException();

        if($pet->getSelfReflectionPoint() < 1)
            throw new UnprocessableEntityHttpException($pet->getName() . ' does not have any Self-reflection Points remaining.');

        $friendId = $request->request->getInt('petId');

        if(!$friendId)
            throw new UnprocessableEntityHttpException('You gotta\' choose a pet to reconcile with!');

        $relationship = $petRelationshipRepository->findOneBy([
            'pet' => $pet->getId(),
            'relationship' => $friendId
        ]);

        if(!$relationship)
            throw new NotFoundHttpException();

        if($relationship->getCurrentRelationship() !== RelationshipEnum::BROKE_UP && $relationship->getCurrentRelationship() !== RelationshipEnum::DISLIKE)
            throw new UnprocessableEntityHttpException('Those pets are totally okay with each other already!');

        $friend = $relationship->getRelationship();

        $otherSide = $petRelationshipRepository->findOneBy([
            'pet' => $friend,
            'relationship' => $pet
        ]);

        if(!$otherSide)
            throw new \Exception($pet->getName() . ' knows ' . $friend->getName() . ', but not the other way around! This is a terrible bug! Make Ben fix it!');

        $possibleRelationships = $petRelationshipService->getRelationshipsBetween(
            $petRelationshipService->max($relationship->getRelationshipGoal(), RelationshipEnum::FRIEND),
            $petRelationshipService->max($otherSide->getRelationshipGoal(), RelationshipEnum::FRIEND)
        );

        $newRelationship = $squirrel3->rngNextFromArray($possibleRelationships);

        $minimumCommitment = $petRelationshipService->generateInitialCommitment($newRelationship, $newRelationship);

        $relationshipDescriptions = [
            RelationshipEnum::FRIEND => 'friends',
            RelationshipEnum::BFF => 'BFFs',
            RelationshipEnum::FWB => 'FWBs',
            RelationshipEnum::MATE => 'dating'
        ];

        $relationship
            ->setCurrentRelationship($newRelationship)
            ->setRelationshipGoal($newRelationship)
            ->setCommitment(max($relationship->getCommitment(), $minimumCommitment))
        ;

        $responseService->createActivityLog($pet, $pet->getName() . ' and ' . $friend->getName() . ' talked and made up! They are now ' . $relationshipDescriptions[$newRelationship ] . '!', 'icons/activity-logs/friend')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        $otherSide
            ->setCurrentRelationship($newRelationship)
            ->setRelationshipGoal($newRelationship)
            ->setCommitment(max($otherSide->getCommitment(), $minimumCommitment))
        ;

        $friendActivityLog = $responseService->createActivityLog($friend, $pet->getName() . ' came over; they talked with ' . $friend->getName() . ', and the two made up! They are now ' . $relationshipDescriptions[$newRelationship ] . '!', 'icons/activity-logs/friend')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        // don't show the message "twice"
        if($pet->getOwner()->getId() === $friend->getOwner()->getId())
            $friendActivityLog->setViewed();

        $pet->increaseSelfReflectionPoint(-1);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/logs/calendar/{year}/{month}", methods={"GET"}, requirements={"pet":"\d+", "year":"\d+", "month":"\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function logCalendar(
        ResponseService $responseService, PetActivityLogRepository $petActivityLogRepository,

        // route arguments:
        Pet $pet, ?int $year = null, ?int $month = null
    )
    {
        if($year === null && $month === null)
        {
            $year = (int)date('Y');
            $month = (int)date('n');
        }

        if($month < 1 || $month > 12)
            throw new UnprocessableEntityHttpException('"month" must be between 1 and 12!');

        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new AccessDeniedHttpException();

        $results = $petActivityLogRepository->findLogsForPetByDate($pet, $year, $month);

        return $responseService->success([
            'year' => $year,
            'month' => $month,
            'calendar' => $results
        ]);
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

        $petActivityLogsFilterService->addRequiredFilter('pet', $pet->getId());

        return $responseService->success(
            $petActivityLogsFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::PET_ACTIVITY_LOGS ]
        );
    }

    /**
     * @Route("/{pet}/activityStats", methods={"GET"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function activityStats(
        Pet $pet, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new AccessDeniedHttpException();

        $stats = $pet->getPetActivityStats();

        if($stats === null)
            return $responseService->success(null);

        $data = [
            'byTime' => [],
            'byActivity' => [],
            'byActivityCombined' => [],
        ];

        $byTimeTotal = 0;
        $byActivityTotal = 0;
        $byActivityCombinedTotal = 0;

        foreach(PetActivityStatEnum::getValues() as $stat)
        {
            if(in_array($stat, PetActivityStatsService::STATS_THAT_CANT_FAIL))
            {
                $data['byActivity'][] = [
                    'value' => $stats->{'get' . $stat}(),
                    'deleted' => 0,
                    'label' => PetActivityStatsService::STAT_LABELS[$stat],
                    'color' => PetActivityStatsService::STAT_COLORS[$stat]
                ];

                $data['byActivityCombined'][] = [ 'value' => $stats->{'get' . $stat}(), 'label' => PetActivityStatsService::STAT_LABELS[$stat], 'color' => PetActivityStatsService::STAT_COLORS[$stat] ];

                $byActivityTotal += $stats->{'get' . $stat}();
                $byActivityCombinedTotal += $stats->{'get' . $stat}();
            }
            else
            {
                $success = $stats->{'get' . $stat . 'success'}();
                $failure = $stats->{'get' . $stat . 'failure'}();

                $data['byActivity'][] = [
                    'value' => $success + $failure,
                    'deleted' => $failure,
                    'label' => PetActivityStatsService::STAT_LABELS[$stat],
                    'color' => PetActivityStatsService::STAT_COLORS[$stat]
                ];

                $data['byActivityCombined'][] = [ 'value' => $success + $failure, 'label' => PetActivityStatsService::STAT_LABELS[$stat], 'color' => PetActivityStatsService::STAT_COLORS[$stat] ];

                $byActivityTotal += $success + $failure;
                $byActivityCombinedTotal += $success + $failure;
            }

            $data['byTime'][] = [ 'value' => $stats->{'get' . $stat . 'time'}(), 'label' => PetActivityStatsService::STAT_LABELS[$stat], 'color' => PetActivityStatsService::STAT_COLORS[$stat] ];

            $byTimeTotal += $stats->{'get' . $stat . 'time'}();
        }

        $data['byActivity'] = array_map(function($a) use($byActivityTotal) {
            return [
                'label' => $a['label'],
                'value' => $a['value'] > 0 ? ($a['value'] / $byActivityTotal) : 0,
                'percentDeleted' => $a['value'] > 0 ? ($a['deleted'] / $a['value']) : 0,
                'color' => $a['color'],
            ];
        }, $data['byActivity']);

        $data['byActivityCombined'] = array_map(function($a) use($byActivityCombinedTotal) {
            return [
                'label' => $a['label'],
                'value' => $byActivityCombinedTotal > 0 ? ($a['value'] / $byActivityCombinedTotal) : 0,
                'color' => $a['color'],
            ];
        }, $data['byActivityCombined']);

        $data['byTime'] = array_map(function($a) use($byTimeTotal) {
            return [
                'label' => $a['label'],
                'value' => $byTimeTotal > 0 ? ($a['value'] / $byTimeTotal) : 0,
                'color' => $a['color']
            ];
        }, $data['byTime']);

        // the chart order is important; the transition from one chart to the next (in order) teaches what the charts mean
        return $responseService->success([
            [
                'title' => 'Activities, by Time Spent',
                'data' => $data['byTime'],
            ],
            [
                'title' => 'Activities, by Count',
                'data' => $data['byActivityCombined'],
            ],
            [
                'title' => 'Activity Success vs Failure',
                'data' => $data['byActivity'],
            ],
        ]);
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
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
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
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
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

        if(count($items) !== count($inventory))
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
            [ SerializationGroupEnum::MY_PET ]
        );
    }

    /**
     * @Route("/{pet}/pickTalent", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pickTalent(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        MeritRepository $meritRepository
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('That\'s not your pet.');

        if($pet->getCanPickTalent() !== 'talent')
            throw new AccessDeniedHttpException('This pet is not ready to have a talent picked.');

        $talent = $request->request->get('talent', '');

        if(!in_array($talent, [ MeritEnum::MIND_OVER_MATTER, MeritEnum::MATTER_OVER_MIND, MeritEnum::MODERATION ]))
            throw new UnprocessableEntityHttpException('You gotta\' choose one of the talents!');

        $merit = $meritRepository->findOneByName($talent);

        if(!$merit)
            throw new \Exception('Programmer error! The Merit "' . $talent . '" does not exist in the DB! :(');

        $pet->addMerit($merit);

        if($talent === MeritEnum::MIND_OVER_MATTER)
        {
            $pet->getSkills()
                ->increaseStat('intelligence')
                ->increaseStat('perception')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception', 'dexterity' ]))
            ;
        }
        else if($talent === MeritEnum::MATTER_OVER_MIND)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina', 'dexterity' ]))
            ;
        }
        else if($talent === MeritEnum::MODERATION)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')
                ->increaseStat('intelligence')
                ->increaseStat('perception')
            ;
        }

        $pet->getSkills()->setTalent();

        $responseService->createActivityLog($pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()), '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/pickExpertise", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pickExpertise(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        MeritRepository $meritRepository
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('That\'s not your pet.');

        if($pet->getCanPickTalent() !== 'expertise')
            throw new AccessDeniedHttpException('This pet is not ready to have a talent picked.');

        $expertise = $request->request->get('expertise', '');

        if(!in_array($expertise, [ MeritEnum::FORCE_OF_WILL, MeritEnum::FORCE_OF_NATURE, MeritEnum::BALANCE ]))
            throw new UnprocessableEntityHttpException('You gotta\' choose one of the talents!');

        $merit = $meritRepository->findOneByName($expertise);

        if(!$merit)
            throw new \Exception('Programmer error! The Merit "' . $expertise . '" does not exist in the DB! :(');

        $pet->addMerit($merit);

        if($expertise === MeritEnum::FORCE_OF_WILL)
        {
            $pet->getSkills()
                ->increaseStat('intelligence')
                ->increaseStat('perception')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'intelligence', 'perception', 'dexterity' ]))
            ;
        }
        else if($expertise === MeritEnum::FORCE_OF_NATURE)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')

                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina' ]))
                ->increaseStat($squirrel3->rngNextFromArray([ 'strength', 'stamina', 'dexterity' ]))
            ;
        }
        else if($expertise === MeritEnum::BALANCE)
        {
            $pet->getSkills()
                ->increaseStat('strength')
                ->increaseStat('stamina')
                ->increaseStat('dexterity')
                ->increaseStat('intelligence')
                ->increaseStat('perception')
            ;
        }

        $pet->getSkills()->setExpertise();

        $responseService->createActivityLog($pet, str_replace('%pet.name%', $pet->getName(), $merit->getDescription()), '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::LEVEL_UP)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
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

            return $responseService->success($suggestions, [ SerializationGroupEnum::MY_PET ]);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @Route("/typeahead/troubledRelationships", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function troubledRelationshipsTypeaheadSearch(
        Request $request, ResponseService $responseService, PetRepository $petRepository,
        PetRelationshipTypeaheadService $petRelationshipTypeaheadService, PetRelationshipService $petRelationshipService
    )
    {
        $user = $this->getUser();

        $petId = $request->query->getInt('petId');

        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        $petRelationshipTypeaheadService->setParameters($pet, [
            RelationshipEnum::BROKE_UP,
            RelationshipEnum::DISLIKE
        ]);

        try
        {
            $suggestions = array_map(function(Pet $otherPet) use($petRelationshipService, $pet) {
                $possibleRelationships = $petRelationshipService->getRelationshipsBetween(
                    $petRelationshipService->max(RelationshipEnum::FRIEND, $otherPet->getRelationshipWith($pet)->getRelationshipGoal()),
                    $petRelationshipService->max(RelationshipEnum::FRIEND, $pet->getRelationshipWith($otherPet)->getRelationshipGoal())
                );

                return [
                    'pet' => $otherPet,
                    'possibleRelationships' => $possibleRelationships
                ];
            }, $petRelationshipTypeaheadService->search('name', $request->query->get('search', '')));

            return $responseService->success($suggestions, [ SerializationGroupEnum::PET_PUBLIC_PROFILE ]);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @Route("/{pet}/guessFavoriteFlavor", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function guessFavoriteFlavor(
        Pet $pet, Request $request, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        InventoryService $inventoryService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('That\'s not your pet.');

        if($pet->getRevealedFavoriteFlavor())
            throw new UnprocessableEntityHttpException($pet->getName() . '\'s favorite flavor has already been revealed!');

        $guess = strtolower(trim($request->request->getAlpha('flavor')));

        if(!FlavorEnum::isAValue($guess))
            throw new UnprocessableEntityHttpException('Please pick a flavor.');

        $flavorGuesses = $userQuestRepository->findOrCreate($user, 'Flavor Guesses for Pet #' . $pet->getId(), 0);

        if($flavorGuesses->getValue() > 0 && $flavorGuesses->getLastUpdated()->format('Y-m-d') === date('Y-m-d'))
            throw new AccessDeniedHttpException('You already guessed today. Try again tomorrow.');

        $flavorGuesses->setValue($flavorGuesses->getValue() + 1);

        $data = null;

        if($pet->getFavoriteFlavor() === $guess)
        {
            $pet
                ->setRevealedFavoriteFlavor($flavorGuesses->getValue())
                ->increaseAffectionLevel(1)
            ;
            $inventoryService->receiveItem('Heartstone', $user, $user, $user->getName() . ' received this from ' . $pet->getName() . ' for knowing their favorite flavor: ' . $pet->getFavoriteFlavor() . '!', LocationEnum::HOME);
            $responseService->setReloadInventory();
            $responseService->addFlashMessage('A Heartstone materializes in front of ' . $pet->getName() . '\'s body, and floats into your hands!');
            $data = $pet;
        }
        else
        {
            $responseService->addFlashMessage('Hm... it seems that wasn\'t correct. ' . $pet->getName() . ' looks a little disappointed. (You can try again, tomorrow.)');
        }

        $em->flush();

        return $responseService->success($data, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/merits", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMerits(Pet $pet, ResponseService $responseService)
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('That\'s not your pet.');

        return $responseService->success($pet->getMerits(), [ SerializationGroupEnum::MERIT_ENCYCLOPEDIA ]);
    }
}
