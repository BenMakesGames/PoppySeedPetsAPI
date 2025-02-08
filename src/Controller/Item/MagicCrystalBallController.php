<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ItemRepository;
use App\Functions\StatusEffectHelpers;
use App\Service\AdoptionService;
use App\Service\Clock;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/magicCrystalBall")]
class MagicCrystalBallController extends AbstractController
{
    #[Route("/{inventory}/createFate", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    function createFate(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        Request $request, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicCrystalBall');

        $petId = $request->request->getInt('petId', 0);

        if($petId < 1)
            throw new PSPFormValidationException('You must select a pet.');

        /** @var Pet|null $pet */
        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user->getId(),
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        $fate = $rng->rngNextFromArray([
            [
                'statusEffect' => StatusEffectEnum::FATED_DELICIOUSNESS,
                'description' => 'a secret chamber containing a wealth of exotic foods and other treasures',
            ],
            [
                'statusEffect' => StatusEffectEnum::FATED_SOAKEDLY,
                'description' => 'a sunken ship with a locked safe'
            ],
            [
                'statusEffect' => StatusEffectEnum::FATED_ELECTRICALLY,
                'description' => 'an electric shock'
            ],
            [
                'statusEffect' => StatusEffectEnum::FATED_FERALLY,
                'description' => 'a lone wolf with intense eyes'
            ],
            [
                'statusEffect' => StatusEffectEnum::FATED_LUNARLY,
                'description' => 'a swirling cloud of moths'
            ]
        ]);

        StatusEffectHelpers::applyStatusEffect($em, $pet, $fate['statusEffect'], 1);

        $goldBar = ItemRepository::findOneByName($em, 'Gold Bar');

        $inventory
            ->changeItem($goldBar)
            ->addComment('This is all that remains of a Magic Crystal Ball...');

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->success([
            'description' => $fate['description']
        ]);
    }

    #[Route("/{inventory}/predictOffspring", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    function predictOffspring(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        Request $request
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicCrystalBall');

        $petId = $request->request->getInt('petId', 0);

        if($petId < 1)
            throw new PSPFormValidationException('You must select a pet.');

        /** @var Pet|null $pet */
        $pet = $em->getRepository(Pet::class)->findOneBy([
            'id' => $petId,
            'owner' => $user->getId(),
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        if(!$pet->getPregnancy())
            throw new PSPFormValidationException($pet->getName() . ' is not pregnant!');

        $goldBar = ItemRepository::findOneByName($em, 'Gold Bar');

        $inventory
            ->changeItem($goldBar)
            ->addComment('This is all that remains of a Magic Crystal Ball...');

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->success([
            'colorA' => $pet->getPregnancy()->getColorA(),
            'colorB' => $pet->getPregnancy()->getColorB(),
            'speciesImage' => $pet->getPregnancy()->getSpecies()->getImage()
        ]);
    }

    #[Route("/{inventory}/findNextRarePetDay", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    function findNextRarePetDay(Inventory $inventory, ResponseService $responseService, Clock $clock, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'magicCrystalBall');

        $oneDay = \DateInterval::createFromDateString('1 day');

        $day = $clock->now;

        while(true)
        {
            $day = $day->add($oneDay);

            if(AdoptionService::isRarePetDay($day))
                break;
        }

        $goldBar = ItemRepository::findOneByName($em, 'Gold Bar');

        $inventory
            ->changeItem($goldBar)
            ->addComment('This is all that remains of a Magic Crystal Ball...');

        $em->flush();

        $responseService->setReloadInventory();

        return $responseService->success([
            'date' => $day,
        ]);
    }

}