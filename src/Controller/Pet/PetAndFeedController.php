<?php
namespace App\Controller\Pet;

use App\Controller\PoppySeedPetsController;
use App\Entity\Guild;
use App\Entity\Merit;
use App\Entity\Pet;
use App\Entity\PetRelationship;
use App\Entity\SpiritCompanion;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\RelationshipEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\GuildRepository;
use App\Repository\InventoryRepository;
use App\Repository\MeritRepository;
use App\Repository\PetActivityLogRepository;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\Filter\PetActivityLogsFilterService;
use App\Service\Filter\PetFilterService;
use App\Service\Filter\PetRelationshipFilterService;
use App\Service\InventoryService;
use App\Service\MeritService;
use App\Service\PetActivity\EatingService;
use App\Service\PetActivityStatsService;
use App\Service\PetAndPraiseService;
use App\Service\PetAssistantService;
use App\Service\PetRelationshipService;
use App\Service\ProfanityFilterService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\Typeahead\PetRelationshipTypeaheadService;
use App\Service\Typeahead\PetTypeaheadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/pet")
 */
class PetAndFeedController extends PoppySeedPetsController
{
    /**
     * @Route("/{pet}/pet", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $rng,
        PetAndPraiseService $petAndPraiseService
    )
    {
        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('You can\'t pet that pet.');

        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        try
        {
            $petAndPraiseService->doPet($pet);
        }
        catch(\InvalidArgumentException $e)
        {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $em->flush();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
        {
            return $responseService->success([ 'pet' => $pet ], [ SerializationGroupEnum::MY_PET ]);
        }
        else
        {
            $emojis = $pet->getAffectionExpressions();
            $emoji = \mb_substr($emojis, $rng->rngNextInt(0, \mb_strlen($emojis) - 1), 1);

            return $responseService->success([ 'pet' => $pet, 'emoji' => $emoji ], [ SerializationGroupEnum::MY_PET ]);
        }
    }

    /**
     * @Route("/{pet}/feed", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feed(
        Pet $pet, Request $request, InventoryRepository $inventoryRepository, ResponseService $responseService,
        EntityManagerInterface $em, EatingService $eatingService
    )
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $this->getUser()->getId())
            throw new AccessDeniedHttpException('You can\'t feed that pet.');

        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

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
            $eatingService->doFeed($pet, $inventory);
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
}
