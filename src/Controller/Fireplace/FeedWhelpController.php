<?php
declare(strict_types=1);

namespace App\Controller\Fireplace;

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\DragonRepository;
use App\Functions\RequestFunctions;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/fireplace")]
class FeedWhelpController extends AbstractController
{
    #[Route("/feedWhelp", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function feedWhelp(
        Request $request, ResponseService $responseService,
        InventoryService $inventoryService, EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $whelp = DragonRepository::findWhelp($em, $user);

        if(!$whelp)
            throw new PSPNotUnlockedException('Dragon Whelp');

        $itemIds = RequestFunctions::getUniqueIdsOrThrow($request, 'food', 'No items were selected as food???');

        $items = $em->getRepository(Inventory::class)->findBy([
            'id' => $itemIds,
            'owner' => $user->getId(),
            'location' => LocationEnum::HOME
        ]);

        $items = array_filter($items, function(Inventory $i) {
            return $i->getItem()->getFood() && (
                $i->getItem()->getFood()->getFishy() > 0 || // most foods you feed the whelp are probably fishy
                $i->getItem()->getFood()->getMeaty() > 0 ||
                $i->getItem()->getFood()->getSpicy() > 0
            );
        });

        if(count($items) < count($itemIds))
            throw new PSPNotFoundException('Some of the food items selected could not be found. That shouldn\'t happen. Reload and try again, maybe?');

        $loot = [];

        foreach($items as $item)
        {
            $em->remove($item);

            $whelp->increaseFood($item->getItem()->getFood()->getFood() + $item->getItem()->getFood()->getSpicy() * 2);

            while($whelp->getFood() >= Dragon::FOOD_REQUIRED_FOR_A_MEAL)
            {
                $whelp->decreaseFood();

                $r = $squirrel3->rngNextInt(1, 100);

                if($r === 1)
                    $loot[] = 'Firestone';          // 1%
                else if($r === 2 || $r === 3)
                    $loot[] = 'Dark Matter';        // 2%
                else if($r <= 8)
                    $loot[] = 'Charcoal';           // 5%
                else if($r <= 28)
                    $loot[] = 'Quintessence';       // 20%
                else
                    $loot[] = 'Liquid-hot Magma';   // 72%
            }
        }

        if(count($loot) > 0)
        {
            sort($loot);

            foreach($loot as $item)
                $inventoryService->receiveItem($item, $user, $user, $whelp->getName() . ' spit this up.', LocationEnum::HOME);

            $responseService->addFlashMessage($whelp->getName() . ' spit up ' . ArrayFunctions::list_nice($loot) . '.');
        }
        else
        {
            $adverb = $squirrel3->rngNextFromArray([
                'happily', 'happily', 'happily', 'excitedly', 'blithely', 'eagerly'
            ]);

            $responseService->addFlashMessage($whelp->getName() . ' ' . $adverb . ' devoured your offering.');
        }

        if($whelp->getGrowth() >= 35 * 20)
        {
            $greetingsAndThanks = $squirrel3->rngNextSubsetFromArray(Dragon::GREETINGS_AND_THANKS, 2);

            $whelp
                ->setIsAdult(true)
                ->setGreetings([ $greetingsAndThanks[0]['greeting'], $greetingsAndThanks[1]['greeting'] ])
                ->setThanks([ $greetingsAndThanks[0]['thanks'], $greetingsAndThanks[1]['thanks'] ])
            ;

            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::DragonDen);

            $responseService->addFlashMessage($whelp->getName() . ' is a whelp no longer! They leave your fireplace and establish a den nearby! (The Dragon Den is now available! Check it out in the menu!)');
        }

        $em->flush();

        if($whelp->getIsAdult())
            return $responseService->success();
        else
            return $responseService->success($whelp, [ SerializationGroupEnum::MY_FIREPLACE ]);
    }
}
