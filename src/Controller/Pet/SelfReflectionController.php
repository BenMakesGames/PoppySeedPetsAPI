<?php
namespace App\Controller\Pet;

use App\Entity\Guild;
use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\RelationshipEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetActivityLogFactory;
use App\Repository\GuildRepository;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use App\Service\IRandom;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use App\Service\Typeahead\PetRelationshipTypeaheadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class SelfReflectionController extends AbstractController
{
    #[Route("/{pet}/selfReflection", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGuildMembership(
        Pet $pet, ResponseService $responseService, GuildRepository $guildRepository,
        PetRelationshipRepository $petRelationshipRepository, PetRelationshipService $petRelationshipService
    )
    {
        // just to prevent scraping (this endpoint is currently - 2020-06-29 - used only for changing a pet's guild)
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new PSPPetNotFoundException();

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
                    $possibleRelationships = PetRelationshipService::getRelationshipsBetween(
                        PetRelationshipService::max(RelationshipEnum::FRIEND, $r->getRelationshipGoal()),
                        PetRelationshipService::max(RelationshipEnum::FRIEND, $r->getRelationship()->getRelationshipWith($r->getPet())->getRelationshipGoal())
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
     * @Route("/{pet}/selfReflection/changeGuild", methods={"POST"}, requirements={"pet"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changeGuild(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        GuildRepository $guildRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getSelfReflectionPoint() < 1)
            throw new PSPInvalidOperationException($pet->getName() . ' does not have any Self-reflection Points remaining.');

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It\'s uninterested in changing Guilds.');

        if(!$pet->getGuildMembership())
            throw new PSPInvalidOperationException($pet->getName() . ' isn\'t in a guild!');

        $guildId = $request->request->getInt('guildId');

        if(!$guildId)
            throw new PSPFormValidationException('You gotta\' choose a guild!');

        $guild = $guildRepository->find($guildId);

        if(!$guild)
            throw new PSPNotFoundException('That guild does not exist!');

        if($pet->getGuildMembership()->getGuild()->getId() === $guild->getId())
            throw new PSPInvalidOperationException($pet->getName() . ' is already a member of ' . $guild->getName() . '...');

        $oldGuildName = $pet->getGuildMembership()->getGuild()->getName();

        $pet->getGuildMembership()
            ->setGuild($guild)
        ;

        $pet->increaseSelfReflectionPoint(-1);

        PetActivityLogFactory::createUnreadLog($em, $pet, $pet->getName() . ' left ' . $oldGuildName . ', and joined ' . $guild->getName() . '!')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/selfReflection/reconcile", methods={"POST"}, requirements={"pet"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function reconcileWithAnotherPet(
        Pet $pet, Request $request, ResponseService $responseService, PetRelationshipRepository $petRelationshipRepository,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getSelfReflectionPoint() < 1)
            throw new PSPInvalidOperationException($pet->getName() . ' does not have any Self-reflection Points remaining.');

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It\'s uninterested in reconciling.');

        $friendId = $request->request->getInt('petId');

        if(!$friendId)
            throw new PSPFormValidationException('You gotta\' choose a pet to reconcile with!');

        $relationship = $petRelationshipRepository->findOneBy([
            'pet' => $pet->getId(),
            'relationship' => $friendId
        ]);

        if(!$relationship)
            throw new PSPNotFoundException('Those pets don\'t seem to have a relationship of any kind...');

        if($relationship->getCurrentRelationship() !== RelationshipEnum::BROKE_UP && $relationship->getCurrentRelationship() !== RelationshipEnum::DISLIKE)
            throw new PSPInvalidOperationException('Those pets are totally okay with each other already!');

        $friend = $relationship->getRelationship();

        $otherSide = $petRelationshipRepository->findOneBy([
            'pet' => $friend,
            'relationship' => $pet
        ]);

        if(!$otherSide)
            throw new \Exception($pet->getName() . ' knows ' . $friend->getName() . ', but not the other way around! This is a terrible bug! Make Ben fix it!');

        $possibleRelationships = PetRelationshipService::getRelationshipsBetween(
            PetRelationshipService::max($relationship->getRelationshipGoal(), RelationshipEnum::FRIEND),
            PetRelationshipService::max($otherSide->getRelationshipGoal(), RelationshipEnum::FRIEND)
        );

        $newRelationship = $squirrel3->rngNextFromArray($possibleRelationships);

        $minimumCommitment = PetRelationshipService::generateInitialCommitment($squirrel3, $newRelationship, $newRelationship);

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

        PetActivityLogFactory::createUnreadLog($em, $pet, $pet->getName() . ' and ' . $friend->getName() . ' talked and made up! They are now ' . $relationshipDescriptions[$newRelationship ] . '!')
            ->setIcon('icons/activity-logs/friend')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        $otherSide
            ->setCurrentRelationship($newRelationship)
            ->setRelationshipGoal($newRelationship)
            ->setCommitment(max($otherSide->getCommitment(), $minimumCommitment))
        ;

        $makeUpMessage = $pet->getName() . ' came over; they talked with ' . $friend->getName() . ', and the two made up! They are now ' . $relationshipDescriptions[$newRelationship ] . '!';

        $friendActivityLog = $pet->getOwner()->getId() === $friend->getOwner()->getId()
            ? PetActivityLogFactory::createReadLog($em, $friend, $makeUpMessage)
            : PetActivityLogFactory::createUnreadLog($em, $friend, $makeUpMessage);

        $friendActivityLog
            ->setIcon('icons/activity-logs/friend')
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE);

        $pet->increaseSelfReflectionPoint(-1);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    #[Route("/typeahead/troubledRelationships", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function troubledRelationshipsTypeaheadSearch(
        Request $request, ResponseService $responseService, PetRepository $petRepository,
        PetRelationshipTypeaheadService $petRelationshipTypeaheadService, PetRelationshipService $petRelationshipService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $petId = $request->query->getInt('petId', 0);

        if($petId <= 0)
            throw new PSPFormValidationException('You gotta\' choose a pet!');

        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $petRelationshipTypeaheadService->setParameters($pet, [
            RelationshipEnum::BROKE_UP,
            RelationshipEnum::DISLIKE
        ]);

        $suggestions = array_map(function(Pet $otherPet) use($petRelationshipService, $pet) {
            $possibleRelationships = PetRelationshipService::getRelationshipsBetween(
                PetRelationshipService::max(RelationshipEnum::FRIEND, $otherPet->getRelationshipWith($pet)->getRelationshipGoal()),
                PetRelationshipService::max(RelationshipEnum::FRIEND, $pet->getRelationshipWith($otherPet)->getRelationshipGoal())
            );

            return [
                'pet' => $otherPet,
                'possibleRelationships' => $possibleRelationships
            ];
        }, $petRelationshipTypeaheadService->search('name', $request->query->get('search', '')));

        return $responseService->success($suggestions, [ SerializationGroupEnum::PET_PUBLIC_PROFILE ]);
    }
}
