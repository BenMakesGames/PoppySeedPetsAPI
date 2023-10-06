<?php
namespace App\Controller\Fireplace;

use App\Entity\Fireplace;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\PlayerLogHelpers;
use App\Functions\RequestFunctions;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/fireplace")
 */
class FeedController extends AbstractController
{
    /**
     * @Route("/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedFireplace(
        Request $request, InventoryRepository $inventoryRepository, ResponseService $responseService,
        EntityManagerInterface $em, InventoryService $inventoryService, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $itemIds = RequestFunctions::getUniqueIdsOrThrow($request, 'fuel', 'No items were selected as fuel???');

        $items = $inventoryRepository->findFuel($user, $itemIds);

        if(count($items) < count($itemIds))
            throw new PSPNotFoundException('Some of the fuel items selected could not be found. That shouldn\'t happen. Reload and try again, maybe?');

        $fireplace = $user->getFireplace();

        $fuelNotUsed = [];

        $fuelUsed = [];

        foreach($items as $item)
        {
            // don't feed an item if doing so would waste more than half the item's fuel
            if($fireplace->getHeat() + $item->getItem()->getFuel() / 2 <= Fireplace::MAX_HEAT)
            {
                $alcohol = $item->getItem()->getFood() ? $item->getItem()->getFood()->getAlcohol() * 4 : 0;
                $fireplace->addFuel($item->getItem()->getFuel(), $alcohol);
                $em->remove($item);
                $fuelUsed[] = $item->getFullItemName();
            }
            else
            {
                $fuelNotUsed[] = $item->getItem()->getName();
            }
        }

        if(count($fuelUsed) > 0)
        {
            $entry = count($fuelUsed) == 1
                ? 'You burned ' . $fuelUsed[0] . ' for fuel in the Fireplace.'
                : 'You burned the following items for fuel in the Fireplace: ' . ArrayFunctions::list_nice($fuelUsed) . '.';

            PlayerLogHelpers::create($em, $user, $entry, [ 'Fireplace' ]);

            UserStatsRepository::incrementStat($em, $user, UserStatEnum::ITEMS_THROWN_INTO_THE_FIREPLACE, count($fuelUsed));
        }

        if($fireplace->getHelper() && $fireplace->getSoot() >= 18 * 60)
        {
            $helper = $fireplace->getHelper();
            $petWithSkills = $helper->getComputedSkills();
            $fireplace->cleanSoot(18 * 60);

            $skill = ($petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal()) * 2 + $petWithSkills->getClimbingBonus()->getTotal();

            $foodItems = [ 'Fried Egg', 'Fried Tomato', 'Pan-fried Tofu', 'Mighty Fried Bananas' ];

            $extraItem = PetAssistantService::getExtraItem(
                $rng,
                $skill,
                [ 'Feathers', 'Fluff', 'Spider', 'Cobweb', 'Gochujang Recipe' ],
                [ 'Silica Grounds', 'Aging Powder', $rng->rngNextFromArray($foodItems) ],
                [ 'Charcoal', 'Glass', 'Spider Roe' ],
                [ 'Coke', 'Magic Smoke' ]
            );

            $loot = $inventoryService->receiveItem($extraItem, $user, $user, $helper->getName() . ' found this in ' . $user->getName() . '\'s Fireplace chimney.', LocationEnum::HOME);

            $message = $helper->getName() . ' found ' . $loot->getItem()->getNameWithArticle() . ' in the chimney!';

            if(in_array($loot->getItem()->getName(), $foodItems))
                $message .= ' (Who\'s frying stuff in here?!)';

            $responseService->addFlashMessage($message);
        }

        $em->flush();

        if(count($fuelNotUsed) > 0)
        {
            $responseService->addFlashMessage(
                'The fireplace can only handle so much fire! Adding the ' . ArrayFunctions::list_nice($fuelNotUsed) .
                ' would be wasteful at this point, so ' . (count($fuelNotUsed) == 1 ? 'it was' : 'they were') . ' not used.'
            );
        }

        return $responseService->success($user->getFireplace(), [
            SerializationGroupEnum::MY_FIREPLACE,
            SerializationGroupEnum::HELPER_PET
        ]);
    }
}
