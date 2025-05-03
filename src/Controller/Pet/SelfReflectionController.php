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


namespace App\Controller\Pet;

use App\Entity\Guild;
use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\RelationshipEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\PetActivityLogFactory;
use App\Service\IRandom;
use App\Service\PetRelationshipService;
use App\Service\ResponseService;
use App\Service\Typeahead\PetRelationshipTypeaheadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/pet")]
class SelfReflectionController
{
    #[Route("/{pet}/selfReflection", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGuildMembership(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, UserAccessor $userAccessor
    ): JsonResponse
    {
        // just to prevent scraping (this endpoint is currently - 2020-06-29 - used only for changing a pet's guild)
        if($pet->getOwner()->getId() !== $userAccessor->getUserOrThrow()->getId())
            throw new PSPPetNotFoundException();

        $guildData = array_map(
            function(Guild $g) {
                return [
                    'id' => $g->getId(),
                    'name' => $g->getName(),
                ];
            },
            $em->getRepository(Guild::class)->findAll()
        );

        $numberDisliked = (int)$em->createQueryBuilder()
            ->select('COUNT(r)')->from(PetRelationship::class, 'r')
            ->andWhere('r.pet=:pet')
            ->andWhere('r.currentRelationship IN (:currentRelationship)')
            ->setParameter('pet', $pet)
            ->setParameter('currentRelationship', [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ])
            ->getQuery()
            ->getSingleScalarResult();

        if($numberDisliked <= 5)
        {
            $relationships = $em->getRepository(PetRelationship::class)->findBy([
                'pet' => $pet,
                'currentRelationship' => [ RelationshipEnum::DISLIKE, RelationshipEnum::BROKE_UP ],
            ], [], $numberDisliked);

            $troubledRelationships = array_map(
                fn(PetRelationship $r) => [
                    'pet' => $r->getRelationship(),
                    'possibleRelationships' => PetRelationshipService::getRelationshipsBetween(
                        PetRelationshipService::max(RelationshipEnum::FRIEND, $r->getRelationshipGoal()),
                        PetRelationshipService::max(RelationshipEnum::FRIEND, $r->getRelationship()->getRelationshipWith($r->getPet())->getRelationshipGoal())
                    )
                ],
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

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/selfReflection/changeGuild", methods: ["POST"], requirements: ["pet" => "\d+"])]
    public function changeGuild(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

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

        $guild = $em->getRepository(Guild::class)->find($guildId);

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

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{pet}/selfReflection/reconcile", methods: ["POST"], requirements: ["pet" => "\d+"])]
    public function reconcileWithAnotherPet(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if($user->getId() !== $pet->getOwner()->getId())
            throw new PSPPetNotFoundException();

        if($pet->getSelfReflectionPoint() < 1)
            throw new PSPInvalidOperationException($pet->getName() . ' does not have any Self-reflection Points remaining.');

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It\'s uninterested in reconciling.');

        $friendId = $request->request->getInt('petId');

        if(!$friendId)
            throw new PSPFormValidationException('You gotta\' choose a pet to reconcile with!');

        $relationship = $em->getRepository(PetRelationship::class)->findOneBy([
            'pet' => $pet->getId(),
            'relationship' => $friendId
        ]);

        if(!$relationship)
            throw new PSPNotFoundException('Those pets don\'t seem to have a relationship of any kind...');

        if($relationship->getCurrentRelationship() !== RelationshipEnum::BROKE_UP && $relationship->getCurrentRelationship() !== RelationshipEnum::DISLIKE)
            throw new PSPInvalidOperationException('Those pets are totally okay with each other already!');

        $friend = $relationship->getRelationship();

        $otherSide = $em->getRepository(PetRelationship::class)->findOneBy([
            'pet' => $friend,
            'relationship' => $pet
        ]);

        if(!$otherSide)
            throw new \Exception($pet->getName() . ' knows ' . $friend->getName() . ', but not the other way around! This is a terrible bug! Make Ben fix it!');

        $possibleRelationships = PetRelationshipService::getRelationshipsBetween(
            PetRelationshipService::max($relationship->getRelationshipGoal(), RelationshipEnum::FRIEND),
            PetRelationshipService::max($otherSide->getRelationshipGoal(), RelationshipEnum::FRIEND)
        );

        $newRelationship = $rng->rngNextFromArray($possibleRelationships);

        $minimumCommitment = PetRelationshipService::generateInitialCommitment($rng, $newRelationship, $newRelationship);

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
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        PetRelationshipTypeaheadService $petRelationshipTypeaheadService, PetRelationshipService $petRelationshipService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        $petId = $request->query->getInt('petId', 0);

        if($petId <= 0)
            throw new PSPFormValidationException('You gotta\' choose a pet!');

        $pet = $em->getRepository(Pet::class)->find($petId);

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
