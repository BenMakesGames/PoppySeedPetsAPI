<?php
namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Enum\SerializationGroupEnum;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use App\Repository\SpiritCompanionRepository;
use App\Service\Filter\PetRelationshipFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/pet")
 */
class RelationshipsController extends AbstractController
{
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
     * @Route("/{pet}/familyTree", methods={"GET"})
     */
    public function getFamilyTree(
        Pet $pet, ResponseService $responseService, PetRepository $petRepository,
        SpiritCompanionRepository $spiritCompanionRepository
    )
    {
        $siblings = $petRepository->findSiblings($pet);
        $parents = $petRepository->findParents($pet);

        $grandparents = [];
        $spiritGrandparents = [];

        foreach($parents as $parent)
        {
            $grandparents = array_merge($grandparents, $petRepository->findParents($parent));

            if($parent->getSpiritDad())
                $spiritGrandparents[] = $parent->getSpiritDad();
        }

        $children = $petRepository->findChildren($pet);

        return $responseService->success([
            'grandparents' => $grandparents,
            'parents' => $parents,
            'spiritGrandparents' => $spiritGrandparents,
            'spiritParent' => $pet->getSpiritDad(),
            'siblings' => $siblings,
            'children' => $children,
        ], [ SerializationGroupEnum::PET_FRIEND, SerializationGroupEnum::PET_SPIRIT_ANCESTOR ]);
    }
}
