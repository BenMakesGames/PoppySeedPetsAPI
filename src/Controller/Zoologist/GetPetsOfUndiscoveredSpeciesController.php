<?php
namespace App\Controller\Zoologist;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\SimpleDb;
use App\Model\FilterResults;
use App\Service\Filter\PetFilterService;
use App\Service\Filter\PetSpeciesFilterService;
use App\Service\Filter\UserSpeciesCollectedFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/zoologist")]
class GetPetsOfUndiscoveredSpeciesController extends AbstractController
{
    #[Route("/showable", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getPets(
        Request $request, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            throw new PSPNotUnlockedException('Zoologist');

        $page = $request->query->getInt('page', 0);

        $resultCount = SimpleDb::createReadOnlyConnection()
            ->query(
                'SELECT count(pet.id)
                FROM pet
                LEFT JOIN pet_species AS species ON pet.species_id=species.id
                LEFT JOIN user_species_collected AS discovered
                    ON species.id=discovered.species_id AND discovered.user_id=pet.owner_id
                WHERE
                    pet.owner_id=:userId
                    AND discovered.id IS NULL
                ',
                [
                    ':userId' => $user->getId(),
                ]
            )
            ->getSingleValue();

        $pets = SimpleDb::createReadOnlyConnection()
            ->query(
                'SELECT pet.id,pet.name,pet.color_a,pet.color_b,pet.scale,species.id AS speciesId,species.name AS speciesName,species.image
                FROM pet
                LEFT JOIN pet_species AS species ON pet.species_id=species.id
                LEFT JOIN user_species_collected AS discovered
                    ON species.id=discovered.species_id AND discovered.user_id=pet.owner_id
                WHERE
                    pet.owner_id=:userId
                    AND discovered.id IS NULL
                LIMIT :offset,20
                ',
                [
                    ':userId' => $user->getId(),
                    ':offset' => $page * 20,
                ]
            )
            ->mapResults(fn($petId, $petName, $petColorA, $petColorB, $petScale, $speciesId, $speciesName, $speciesImage) => [
                'id' => $petId,
                'name' => $petName,
                'colorA' => $petColorA,
                'colorB' => $petColorB,
                'scale' => $petScale,
                'species' => [
                    'id' => $speciesId,
                    'name' => $speciesName,
                    'image' => $speciesImage,
                ],
            ]);

        $results = new FilterResults();

        $results->page = $page;
        $results->pageSize = 20;
        $results->pageCount = ceil($resultCount / 20);
        $results->resultCount = $resultCount;
        $results->results = $pets;

        return $responseService->success($results, [ SerializationGroupEnum::FILTER_RESULTS ]);
    }
}