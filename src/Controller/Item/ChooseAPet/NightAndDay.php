<?php
declare(strict_types=1);

namespace App\Controller\Item\ChooseAPet;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Model\PetChanges;
use App\Service\CommentFormatter;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/nightAndDay")]
class NightAndDay extends AbstractController
{
    #[Route("/{inventory}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function useItem(
        Inventory $inventory,
        Request $request,
        ResponseService $responseService,
        EntityManagerInterface $em,
        InventoryService $inventoryService,
        PetExperienceService $petExperienceService,
        CommentFormatter $commentFormatter,
        IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'nightAndDay');

        $pet = ChooseAPetHelpers::getPet($request, $user, $em);
        $petChanges = new PetChanges($pet);

        $pairOfItems = $rng->rngNextFromArray([
            [ 'Black Baabble', 'White Baabble' ],
            [ 'Black Feathers', 'White Feathers' ],
            [ 'Black Flag', 'White Flag' ],
        ]);

        $subject = $rng->rngNextFromArray([
            'on the duality of night and day; light and dark',
            'on their place in the infinite multiverse',
            'inward'
        ]);

        $messageMiddle = "focused {$subject}, and the {$inventory->getFullItemName()} turned into";
        $itemList = ArrayFunctions::list_nice($pairOfItems);

        $activityLog = PetActivityLogFactory::createReadLog($em, $pet, "%pet:{$pet->getId()}.name% {$messageMiddle} {$itemList}!")
            ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
        ;

        foreach($pairOfItems as $item)
            $inventoryService->petCollectsItem($item, $pet, "{$pet->getName()} {$messageMiddle} this!", $activityLog);

        $petExperienceService->gainExp($pet, 2, [ PetSkillEnum::ARCANA ], $activityLog);

        $activityLog->setChanges($petChanges->compare($pet));

        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(
            $commentFormatter->format($activityLog->getEntry()),
            [ 'itemDeleted' => true ]
        );
    }
}