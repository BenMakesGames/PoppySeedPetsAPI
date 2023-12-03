<?php
namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\StatusEffectEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/item")]
class WolfsFavorController extends AbstractController
{
    private const USER_STAT_NAME = 'Redeemed a Wolf\'s Favor';

    #[Route("/changeWereform/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changePetWereform(
        Inventory $inventory, ResponseService $responseService, Request $request,
        EntityManagerInterface $em, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'changeWereform');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            throw new PSPInvalidOperationException('This pet is not in its wereform.');

        $possibleWereforms = [];

        for($i = 0; $i < 6; $i++)
        {
            if($i != $pet->getWereform())
                $possibleWereforms[] = $i;
        }

        $pet->setWereform($rng->rngNextFromArray($possibleWereforms));

        PetActivityLogFactory::createUnreadLog($em, $pet, ActivityHelpers::PetName($pet) . '\'s wereform has changed!');

        $em->remove($inventory);
        $em->flush();

        return $responseService->success();
    }

    #[Route("/wolfsFavor/{inventory}/furAndClaw", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFluffAndTalons(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wolfsFavor/#/furAndClaw');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $loot = [
            'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff', 'Fluff',
            'Talon', 'Talon', 'Talon', 'Quintessence',
            $rng->rngNextFromArray([
                'Rib', 'Stereotypical Bone',
            ]),
            $rng->rngNextFromArray([
                'Hot Dog', 'Bulbun Plushy'
            ])
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Wolf\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A fluffy pupper drops ' . ArrayFunctions::list_nice($loot) . ' off just outside your door, and bounds off into the distance.', [ 'itemDeleted' => true ]);
    }

    #[Route("/wolfsFavor/{inventory}/theMoon", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMoonStuff(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'wolfsFavor/#/theMoon');

        $location = $inventory->getLocation();

        $loot = [
            'Moon Pearl', 'Moon Pearl', 'Moon Pearl', 'Moon Pearl',
            'Moon Dust', 'Moon Dust',
            'Moth',
            'Meteorite',
        ];

        foreach($loot as $item)
            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' got this from a Wolf\'s Favor.', $location);

        $userStatsRepository->incrementStat($user, self::USER_STAT_NAME);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('A fluffy pupper drops ' . ArrayFunctions::list_nice($loot) . ' off just outside your door, and bounds off into the distance.', [ 'itemDeleted' => true ]);
    }
}
