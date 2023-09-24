<?php
namespace App\Controller\Greenhouse;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\CalendarFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PlayerLogHelpers;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserQuestRepository;
use App\Service\Clock;
use App\Service\InventoryService;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class WeedController extends AbstractController
{
    /**
     * @Route("/weed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function weedPlants(
        ResponseService $responseService, UserQuestRepository $userQuestRepository, EntityManagerInterface $em,
        InventoryService $inventoryService, Squirrel3 $squirrel3, Clock $clock
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $greenhouse = $user->getGreenhouse();

        if(!$greenhouse)
            throw new PSPNotFoundException('You don\'t have a Greenhouse plot.');

        $weeds = $userQuestRepository->findOrCreate($user, 'Greenhouse Weeds', (new \DateTimeImmutable())->modify('-1 minutes')->format('Y-m-d H:i:s'));

        $weedTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $weeds->getValue());

        if($weedTime > new \DateTimeImmutable())
            throw new PSPInvalidOperationException('Your garden\'s doin\' just fine right now, weed-wise.');

        $weeds->setValue((new \DateTimeImmutable())->modify('+18 hours')->format('Y-m-d H:i:s'));

        if($squirrel3->rngNextInt(1, 4) === 1)
            $itemName = $squirrel3->rngNextFromArray([ 'Fluff', 'Red Clover', 'Talon', 'Feathers' ]);
        else
            $itemName = $squirrel3->rngNextFromArray([ 'Dandelion', 'Crooked Stick', 'Crooked Stick' ]);

        $foundItem = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this while weeding their Greenhouse.', LocationEnum::HOME);

        if($greenhouse->isHasFishStatue())
        {
            $possibleItem2s = CalendarFunctions::isSaintPatricksDay($clock->now)
                ? [ '1-leaf Clover', '2-leaf Clover' ]
                : [ 'Algae', 'Scales', 'Freshly-squeezed Fish Oil', 'Silica Grounds' ]
            ;

            $foundItem2 = $inventoryService->receiveItem($squirrel3->rngNextFromArray($possibleItem2s), $user, $user, $user->getName() . ' found this while cleaning their Fish Statue.', LocationEnum::HOME);

            $message = 'You found ' . $foundItem->getItem()->getNameWithArticle() . ' while cleaning up, plus ' . $foundItem2->getItem()->getNameWithArticle() . ' near the Fish Statue!';

        }
        else
        {
            $message = 'You found ' . $foundItem->getItem()->getNameWithArticle() .' while cleaning up!';
        }

        if($greenhouse->getHelper())
        {
            $helper = $greenhouse->getHelper();
            $petWithSkills = $helper->getComputedSkills();
            $skill = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal();

            $hasWaterPlots = $greenhouse->getMaxWaterPlants() > 0;
            $hasDarkPlots = $greenhouse->getMaxDarkPlants() > 0;
            $isRaining = WeatherService::getWeather(new \DateTimeImmutable(), null)->getRainfall() > 0;

            $basicItems = [ 'Egg', 'Blackberries', 'Blueberries', 'Line of Ants' ];
            $slightlyCoolerItems = [ 'Narcissus', 'Plastic', 'Paper', 'Pepino Dulce' ];

            if($hasDarkPlots)
            {
                $basicItems[] = $squirrel3->rngNextFromArray([ 'Toadstool', 'Chanterelle' ]);
            }

            if($hasWaterPlots)
            {
                $basicItems[] = 'Scales';
            }

            if($isRaining)
            {
                $slightlyCoolerItems[] = 'Worms';
            }

            $extraItem = PetAssistantService::getExtraItem(
                $squirrel3,
                $skill,
                $basicItems,
                $slightlyCoolerItems,
                [ 'Coconut', 'Dark Matter', 'Filthy Cloth' ],
                [ 'Mango', 'Gypsum', 'Really Big Leaf', 'White Feathers' ]
            );

            $extraItemObject = ItemRepository::findOneByName($em, $extraItem);

            $surprisingItems = [ 'Coconut', 'Mango' ];
            $litterItems = [ 'Plastic', 'Paper', 'Filthy Cloth' ];

            if(in_array($extraItem, $surprisingItems))
                $extraDetail = '! (As a weed?! Weird!)';
            else if(in_array($extraItem, $litterItems))
                $extraDetail = '! (Weeds are bad enough; what\'s this litter doing here?!)';
            else
                $extraDetail = '.';

            $changes = new PetChanges($helper);
            $activityLogEntry = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . ' weed their Greenhouse, and found ' . $extraItemObject->getNameWithArticle() . $extraDetail);

            $bonusFlower = null;

            if($helper->hasMerit(MeritEnum::GREEN_THUMB))
            {
                $possibleFlowers = [
                    'Agrimony',
                    'Bird\'s-foot Trefoil',
                    'Coriander Flower',
                    'Green Carnation',
                    'Iris',
                    'Narcissus',
                    'Purple Violet',
                    'Red Clover',
                    'Rice Flower',
                    'Viscaria',
                    'Wheat Flower',
                    'Witch-hazel'
                ];

                if($hasWaterPlots)
                    $possibleFlowers[] = 'Lotus Flower';

                $bonusFlower = $squirrel3->rngNextFromArray($possibleFlowers);

                $activityLogEntry->setEntry($activityLogEntry->getEntry() . ' ... oh! And a ' . $bonusFlower . '!');
            }

            $inventoryService->petCollectsItem($extraItemObject, $helper, $helper->getName() . ' found this while weeding the Greenhouse with ' . $user->getName() . $extraDetail, $activityLogEntry);

            if($bonusFlower)
                $inventoryService->petCollectsItem($bonusFlower, $helper, $helper->getName() . ' found this while weeding the Greenhouse with ' . $user->getName() . '.', $activityLogEntry);

            $activityLogEntry
                ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
                ->setChanges($changes->compare($helper))
                ->addTags(PetActivityLogTagRepository::findByNames($em, [ 'Add-on Assistance', 'Greenhouse' ]))
            ;
        }

        $message .= ' ' . $squirrel3->rngNextFromArray([ 'Noice!', 'Yoink!', '👍', '👌', 'Neat-o!', 'Okey dokey!' ]);

        PlayerLogHelpers::create($em, $user, $message, [ 'Greenhouse' ]);

        $em->flush();

        return $responseService->success($message);
    }
}
