<?php
namespace App\Controller;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PlantYieldItem;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PlantTypeEnum;
use App\Enum\PollinatorEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\GrammarFunctions;
use App\Model\PetChanges;
use App\Repository\EnchantmentRepository;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\FieldGuideService;
use App\Service\GreenhouseService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\PetActivity\GreenhouseAdventureService;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class GreenhouseController extends PoppySeedPetsController
{
    public const FORBIDDEN_COMPOST = [
        'Small Bag of Fertilizer',
        'Bag of Fertilizer',
        'Large Bag of Fertilizer',
        'Twilight Fertilizer'
    ];

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getGreenhouse(
        ResponseService $responseService, GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new AccessDeniedHttpException('You haven\'t purchased a Greenhouse plot yet.');

        $greenhouseService->maybeAssignPollinators($user);

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }

    /**
     * @Route("/weed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function weedPlants(
        ResponseService $responseService, UserQuestRepository $userQuestRepository, EntityManagerInterface $em,
        InventoryService $inventoryService, Squirrel3 $squirrel3, PetAssistantService $petAssistantService,
        WeatherService $weatherService, ItemRepository $itemRepository,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $user = $this->getUser();

        $greenhouse = $user->getGreenhouse();

        if(!$greenhouse)
            throw new NotFoundHttpException('You don\'t have a Greenhouse plot.');

        $weeds = $userQuestRepository->findOrCreate($user, 'Greenhouse Weeds', (new \DateTimeImmutable())->modify('-1 minutes')->format('Y-m-d H:i:s'));

        $weedTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $weeds->getValue());

        if($weedTime > new \DateTimeImmutable())
            throw new UnprocessableEntityHttpException('Your garden\'s doin\' just fine right now, weed-wise.');

        $weeds->setValue((new \DateTimeImmutable())->modify('+18 hours')->format('Y-m-d H:i:s'));

        if($squirrel3->rngNextInt(1, 4) === 1)
            $itemName = $squirrel3->rngNextFromArray([ 'Fluff', 'Red Clover', 'Talon', 'Feathers' ]);
        else
            $itemName = $squirrel3->rngNextFromArray([ 'Dandelion', 'Crooked Stick', 'Crooked Stick' ]);

        $foundItem = $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this while weeding their Greenhouse.', LocationEnum::HOME);

        $message = 'You found ' . $foundItem->getItem()->getNameWithArticle() .' while cleaning up!';

        if($greenhouse->getHelper())
        {
            $helper = $greenhouse->getHelper();
            $petWithSkills = $helper->getComputedSkills();
            $skill = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal();

            $hasWaterPlots = $greenhouse->getMaxWaterPlants() > 0;
            $hasDarkPlots = $greenhouse->getMaxDarkPlants() > 0;
            $isRaining = $weatherService->getWeather(new \DateTimeImmutable(), null)->getRainfall() > 0;

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

            $extraItem = $petAssistantService->getExtraItem($skill,
                $basicItems,
                $slightlyCoolerItems,
                [ 'Coconut', 'Dark Matter', 'Filthy Cloth' ],
                [ 'Mango', 'Gypsum', 'Really Big Leaf', 'White Feathers' ]
            );

            $extraItemObject = $itemRepository->findOneByName($extraItem);

            $surprisingItems = [ 'Coconut', 'Mango' ];
            $litterItems = [ 'Plastic', 'Paper', 'Filthy Cloth' ];

            if(in_array($extraItem, $surprisingItems))
                $extraDetail = '! (As a weed?! Weird!)';
            else if(in_array($extraItem, $litterItems))
                $extraDetail = '! (Weeds are bad enough; what\'s this litter doing here?!)';
            else
                $extraDetail = '.';

            $changes = new PetChanges($helper);
            $activityLogEntry = $responseService->createActivityLog($helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . ' weed their Greenhouse, and found ' . $extraItemObject->getNameWithArticle() . $extraDetail, '');
            $inventoryService->petCollectsItem($extraItemObject, $helper, $helper->getName() . ' found this while weeding the Greenhouse with ' . $user->getName() . $extraDetail, $activityLogEntry);

            $activityLogEntry
                ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
                ->setChanges($changes->compare($helper))
                ->addTags($petActivityLogTagRepository->findByNames([ 'Add-on Assistance', 'Greenhouse' ]))
            ;
        }

        $em->flush();

        return $responseService->success($message . ' ' . $squirrel3->rngNextFromArray([ 'Noice!', 'Yoink!', '👍', '👌', 'Neat-o!', 'Okey dokey!' ]));
    }

    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService, GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();

        $petAssistantService->helpGreenhouse($user, $pet);

        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }

    /**
     * @Route("/talkToVisitingBird", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function talkToBird(
        ResponseService $responseService, EntityManagerInterface $em, GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new AccessDeniedHttpException('You haven\'t purchased a Greenhouse plot yet.');

        if(!$user->getGreenhouse()->getVisitingBird())
            throw new NotFoundHttpException('Hm... there\'s no bird here. Reload, maybe??');

        $message = $greenhouseService->approachBird($user->getGreenhouse());

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->success();
    }

    /**
     * @Route("/composter/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedComposter(
        ResponseService $responseService, Request $request, InventoryRepository $inventoryRepository,
        InventoryService $inventoryService, EntityManagerInterface $em, UserStatsRepository $userStatsRepository,
        ItemRepository $itemRepository, SpiceRepository $spiceRepository, Squirrel3 $squirrel3,
        GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new AccessDeniedHttpException('You haven\'t purchased a Greenhouse plot yet!');

        if(!$user->getGreenhouse()->getHasComposter())
            throw new AccessDeniedHttpException('Your don\'t have a composter yet!');

        if(!$request->request->has('food'))
            throw new UnprocessableEntityHttpException('No items were selected as fuel???');

        $itemIds = $request->request->get('food');

        if(!is_array($itemIds)) $itemIds = [ $itemIds ];

        $items = $inventoryRepository->findFertilizers($user, $itemIds);

        $items = array_filter($items, function(Inventory $i)  {
            return !in_array($i->getItem()->getName(), self::FORBIDDEN_COMPOST);
        });

        if(count($items) < count($itemIds))
            throw new UnprocessableEntityHttpException('Some of the compost items selected could not be used. That shouldn\'t happen. Reload and try again, maybe?');

        $totalFertilizer = $user->getGreenhouse()->getComposterFood();

        foreach($items as $item)
            $totalFertilizer += $item->getItem()->getFertilizer();

        $remainingFertilizer = $totalFertilizer;

        $largeBags = (int)($remainingFertilizer / 20);

        $remainingFertilizer -= $largeBags * 20;

        $mediumBags = (int)($remainingFertilizer / 15);

        $remainingFertilizer -= $mediumBags * 15;

        $smallBags = (int)($remainingFertilizer / 10);

        $remainingFertilizer -= $smallBags * 10;

        $itemDelta = $largeBags + $mediumBags + $smallBags - count($items);

        if($itemDelta > 0)
        {
            $itemsAtHome = $inventoryService->countTotalInventory($user, LocationEnum::HOME);

            if($itemsAtHome > 100)
                throw new UnprocessableEntityHttpException('That would leave you with more items at home than you started with, and you\'re already over 100!');

            if($itemsAtHome + $itemDelta > 100)
                throw new UnprocessableEntityHttpException('That would leave you with ' . ($itemsAtHome + $itemDelta) . ' items at home. (100 is the usual limit.)');
        }

        foreach($items as $item)
            $em->remove($item);

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_COMPOSTED, count($items));

        $user->getGreenhouse()
            ->setComposterFood($remainingFertilizer)
            ->decreaseComposterBonusCountdown($totalFertilizer)
        ;

        $bonusItemNames = [];

        while($user->getGreenhouse()->getComposterBonusCountdown() <= 0)
        {
            $user->getGreenhouse()->setComposterBonusCountdown();

            $bonusItem = $itemRepository->findOneByName($squirrel3->rngNextFromArray([
                $squirrel3->rngNextFromArray([ 'Talon', 'Silica Grounds', 'Secret Seashell', 'Brown Bow' ]),
                $squirrel3->rngNextFromArray([ 'Centipede', 'Stink Bug' ]),
                'Grandparoot',
                'Toadstool',
                'String', // let it get rancid
                $squirrel3->rngNextFromArray([ 'Iron Ore', 'Iron Ore', 'Silver Ore', 'Gold Ore', 'Worms' ]),
                'Paper Bag',
            ]));

            $bonusItemNames[] = $bonusItem->getNameWithArticle();

            if($bonusItem->getName() === 'Paper Bag')
                $theBonusItem = $inventoryService->receiveItem($bonusItem, $user, $user, $user->getName() . ' found this in their composter. (Its contents are PROBABLY safe to eat?)', LocationEnum::HOME, false);
            else
                $theBonusItem = $inventoryService->receiveItem($bonusItem, $user, $user, $user->getName() . ' found this in their composter.', LocationEnum::HOME, false);

            if($bonusItem->getName() === 'String' || $bonusItem->getName() === 'Grandparoot' || $bonusItem->getName() === 'Paper Bag')
                $theBonusItem->setSpice($spiceRepository->findOneByName('Rancid'));
        }

        for($i = 0; $i < $largeBags; $i++)
            $inventoryService->receiveItem('Large Bag of Fertilizer', $user, $user, $user->getName() . ' made this using their composter.', LocationEnum::HOME, false);

        for($i = 0; $i < $mediumBags; $i++)
            $inventoryService->receiveItem('Bag of Fertilizer', $user, $user, $user->getName() . ' made this using their composter.', LocationEnum::HOME, false);

        for($i = 0; $i < $smallBags; $i++)
            $inventoryService->receiveItem('Small Bag of Fertilizer', $user, $user, $user->getName() . ' made this using their composter.', LocationEnum::HOME, false);

        $got = [];

        if($largeBags > 0)
            $got[] = $largeBags === 1 ? 'one Large Bag of Fertilizer' : ($largeBags . ' Large Bags of Fertilizer');

        if($mediumBags > 0)
            $got[] = $mediumBags === 1 ? 'one Bag of Fertilizer' : ($mediumBags . ' Bags of Fertilizer');

        if($smallBags > 0)
            $got[] = $smallBags === 1 ? 'one Small Bag of Fertilizer' : ($smallBags . ' Small Bags of Fertilizer');

        $em->flush();

        $thoseOrThat = count($bonusItemNames) === 1 ? 'that' : 'those';

        if(count($got) > 0)
        {
            if(count($bonusItemNames) > 0)
                $responseService->addFlashMessage('You got ' . ArrayFunctions::list_nice($got) . '! Also, ' . ArrayFunctions::list_nice($bonusItemNames) . ' fell out! (Where\'d ' . $thoseOrThat . ' come from?)');
            else
                $responseService->addFlashMessage('You got ' . ArrayFunctions::list_nice($got) . '!');
        }
        else
        {
            if(count($bonusItemNames) > 0)
                $responseService->addFlashMessage('That wasn\'t quite enough to make a bag of fertilizer... but it\'s progress! Oh, and wait, what? ' . ucfirst(ArrayFunctions::list_nice($bonusItemNames)) . ' fell out! (Where\'d ' . $thoseOrThat . ' come from ?)');
            else
                $responseService->addFlashMessage('That wasn\'t quite enough to make a bag of fertilizer... but it\'s progress!');
        }

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }

    /**
     * @Route("/{plant}/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvestPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, UserStatsRepository $userStatsRepository, PetRepository $petRepository,
        UserQuestRepository $userQuestRepository, GreenhouseAdventureService $greenhouseAdventureService,
        GreenhouseService $greenhouseService, SpiceRepository $spiceRepository, Squirrel3 $squirrel3,
        FieldGuideService $fieldGuideService, EnchantmentRepository $enchantmentRepository,
        HattierService $hattierService
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new UnprocessableEntityHttpException('This plant is not yet ready to harvest.');

        if(!$plant->getIsAdult() || $plant->getProgress() < 1)
            throw new UnprocessableEntityHttpException('This plant is not yet ready to harvest.');

        if($plant->getPlant()->getFieldGuideEntry())
            $fieldGuideService->maybeUnlock($user, $plant->getPlant()->getFieldGuideEntry(), '%user:' . $user->getId() . '.Name% harvested a ' . $plant->getPlant()->getName() . '.');

        if(count($plant->getPlant()->getPlantYields()) === 0)
        {
            if($plant->getPlant()->getName() === 'Magic Beanstalk')
            {
                $expandedGreenhouseWithMagicBeanstalk = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse with Magic Bean-stalk', false);

                if(!$expandedGreenhouseWithMagicBeanstalk->getValue())
                {
                    $expandedGreenhouseWithMagicBeanstalk->setValue(true);

                    $user->getGreenhouse()->increaseMaxPlants(1);

                    $em->flush();

                    $responseService->addFlashMessage('You can\'t harvest a Magic Beans-stalk, unfortunately, BUT: your pets might decide to climb up it and explore! Also: you happen to notice that you have another greenhouse plot! (Must be some of that Magic Beans magic!)');

                    return $responseService->success();
                }
            }

            throw new UnprocessableEntityHttpException($plant->getPlant()->getName() . ' cannot be harvested!');
        }

        if($plant->getPlant()->getName() === 'Earth Tree')
        {
            $user->increaseRecyclePoints(25);

            $responseService->addFlashMessage('You carry the tree out to where Tess is planting new trees on the island, and plant the Earth Tree. She gives you 25 recycling points for your help taking care of the Earth!');

            $leaves = $enchantmentRepository->findOneByName('Leafy');

            if(!$hattierService->userHasUnlocked($user, $leaves))
            {
                $hattierService->playerUnlockAura($user, $leaves, 'You unlocked this by replanting an Earth Tree!');
                $responseService->addFlashMessage('As you\'re replanting the Earth Tree, several leaves fall off of it, giving you an idea for a sweet Hattier styling...');
            }
        }

        $pollinators = $plant->getPollinators();

        if($pollinators === PollinatorEnum::BUTTERFLIES)
            $user->getGreenhouse()->setButterfliesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::BEES_1)
            $user->getGreenhouse()->setBeesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::BEES_2)
            $user->getGreenhouse()->setBees2DismissedOn(new \DateTimeImmutable());

        $plant
            ->setPollinators(null)
            ->clearGrowth()
        ;

        if($plant->getPlant()->getName() === 'Barnacle Tree' && DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
        {
            $message = $greenhouseService->makeDapperSwanPet($plant);
        }
        else if($plant->getPlant()->getName() === 'Toadstool Troop' && DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::NEW_MOON)
        {
            $message = $greenhouseService->makeMushroomPet($plant);
        }
        else if($plant->getPlant()->getName() === 'Tomato Plant' && DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
        {
            $message = $greenhouseService->makeTomatePet($plant);
        }
        else
        {
            $plantName = $plant->getPlant()->getName();
            $lootList = [];

            foreach($plant->getPlant()->getPlantYields() as $yield)
            {
                $quantity = $squirrel3->rngNextInt($yield->getMin(), $yield->getMax());

                for($i = 0; $i < $quantity; $i++)
                {
                    /** @var PlantYieldItem $loot */
                    $loot = ArrayFunctions::pick_one_weighted($yield->getItems(), fn(PlantYieldItem $yieldItem) => $yieldItem->getPercentChance());

                    $lootItem = $loot->getItem();
                    $lootItemName = $lootItem->getName();

                    $item = $inventoryService->receiveItem($lootItem, $user, $user, $user->getName() . ' harvested this from ' . GrammarFunctions::indefiniteArticle($plantName) . ' ' . $plantName . '.', LocationEnum::HOME);

                    if($pollinators)
                        $greenhouseService->applyPollinatorSpice($item, $pollinators);

                    if(array_key_exists($lootItemName, $lootList))
                        $lootList[$lootItemName]++;
                    else
                        $lootList[$lootItemName] = 1;
                }
            }

            $harvestBonusMint =
                $plant->getPlant()->getType() === 'earth' &&
                $plant->getPlant()->getName() !== 'Mint Bush' &&
                $user->getGreenhousePlants()->exists(function(int $key, GreenhousePlant $p) {
                    return $p->getPlant()->getName() === 'Mint Bush' && $p->getIsAdult();
                })
            ;

            if($harvestBonusMint)
            {
                $comment = $squirrel3->rngNextInt(1, 4) === 1
                    ? $user->getName() . ' harvested this from ' . GrammarFunctions::indefiniteArticle($plantName) . ' ' . $plantName . '?! (Mint! It gets everywhere!)'
                    : $user->getName() . ' harvested this from ' . GrammarFunctions::indefiniteArticle($plantName) . ' ' . $plantName . '...'
                ;

                $item = $inventoryService->receiveItem('Mint', $user, $user, $comment, LocationEnum::HOME);

                if($pollinators)
                    $greenhouseService->applyPollinatorSpice($item, $pollinators);

                $message = 'You harvested ' . ArrayFunctions::list_nice_quantities($lootList) . '... and some Mint!';
            }
            else
                $message = 'You harvested ' . ArrayFunctions::list_nice_quantities($lootList) . '!';
        }

        $plantsHarvested = $userStatsRepository->incrementStat($user, UserStatEnum::HARVESTED_PLANT);

        if($plantsHarvested->getValue() === 3)
        {
            $user->getGreenhouse()->increaseMaxPlants(3);
            $message .= ' And you\'ve been given three additional plots in the Greenhouse!';
        }
        else if($plantsHarvested->getValue() === 1000)
        {
            $vinesAura = $enchantmentRepository->findOneByName('of Wild Growth');

            $responseService->addFlashMessage('After harvesting the ' . $plant->getPlant()->getName() . ', an odd-looking fairy pops out and bestows a wreath of vines to you. "As you give life to the Earth, you give life to my people. Please, accept this gift for all you have done for us!" (A new style is available at the Hattier\'s! _And_ you somehow got 100 recycling points?! Sure; why not!)');
            $user->increaseRecyclePoints(100);

            $hattierService->playerUnlockAura($user, $vinesAura, 'A fairy gave you this after you harvested your 1000th plant!');
        }
        else
        {
            $eligiblePets = $petRepository->findBy([
                'owner' => $user->getId(),
                'location' => PetLocationEnum::HOME
            ]);

            if($user->getGreenhouse()->getHelper())
                $eligiblePets[] = $user->getGreenhouse()->getHelper();

            $chanceOfHelp = sqrt(count($eligiblePets)) * 100;

            if($squirrel3->rngNextInt(1, 550) <= $chanceOfHelp || $plant->getPlant()->getName() === 'Earth Tree')
            {
                /** @var Pet $helper */
                $helper = $squirrel3->rngNextFromArray($eligiblePets);

                $activity = $greenhouseAdventureService->adventure($helper->getComputedSkills(), $plant);

                if(($pollinators === PollinatorEnum::BEES_1 || $pollinators === PollinatorEnum::BEES_2) && $helper->hasMerit(MeritEnum::BEHATTED))
                {
                    $greenhouseAdventureService->maybeUnlockBeeAura($helper, $activity);
                }
            }
        }

        if($plant->getPlant()->getName() === 'Earth Tree')
            $em->remove($plant);

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }

    /**
     * @Route("/{plant}/fertilize", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function fertilizePlant(
        GreenhousePlant $plant, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository,
        GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new UnprocessableEntityHttpException('This plant is not yet ready to fertilize.');

        $fertilizerId = $request->request->getInt('fertilizer', 0);

        $fertilizer = $inventoryRepository->findOneBy([
            'id' => $fertilizerId,
            'owner' => $user->getId(),
            'location' => Inventory::CONSUMABLE_LOCATIONS,
        ]);

        if(!$fertilizer || $fertilizer->getItem()->getFertilizer() === 0)
            throw new UnprocessableEntityHttpException('A fertilizer must be selected.');

        $plant->increaseGrowth($fertilizer->getItem()->getFertilizer());

        $userStatsRepository->incrementStat($user, UserStatEnum::FERTILIZED_PLANT);

        $em->remove($fertilizer);
        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }

    /**
     * @Route("/{plant}/pullUp", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pullUpPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em, Squirrel3 $squirrel3,
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That plant does not exist.');

        if($plant->getPlant()->getName() === 'Magic Beanstalk')
        {
            $responseService->addFlashMessage('Pulling up the stalk is surprisingly easy, but perhaps more surprising, you find yourself holding Magic Beans, instead of a stalk!');

            $inventoryService->receiveItem('Magic Beans', $user, $user, 'Received by pulling up a Magic Beanstalk, apparently. Magically.', LocationEnum::HOME);
        }
        else if($plant->getPlant()->getName() === 'Goat' && $plant->getIsAdult())
        {
            $responseService->addFlashMessage('The goat, startled, runs into the jungle, shedding a bit of Fluff in the process.');

            $inventoryService->receiveItem('Fluff', $user, $user, 'Dropped by a startled goat.', LocationEnum::HOME);
            if($squirrel3->rngNextInt(1, 2) === 1)
                $inventoryService->receiveItem('Fluff', $user, $user, 'Dropped by a startled goat.', LocationEnum::HOME);
        }

        $pollinators = $plant->getPollinators();

        if($pollinators === PollinatorEnum::BUTTERFLIES)
            $user->getGreenhouse()->setButterfliesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::BEES_1)
            $user->getGreenhouse()->setBeesDismissedOn(new \DateTimeImmutable());
        if($pollinators === PollinatorEnum::BEES_2)
            $user->getGreenhouse()->setBees2DismissedOn(new \DateTimeImmutable());

        $em->remove($plant);
        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/seeds/{type}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getSeeds(
        ResponseService $responseService, InventoryRepository $inventoryRepository,
        string $type = PlantTypeEnum::EARTH
    )
    {
        if(!PlantTypeEnum::isAValue($type))
            throw new UnprocessableEntityHttpException('Must provide a valid seed type ("earth", "water", etc...)');

        $user = $this->getUser();

        $seeds = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.plant', 'plant')
            ->andWhere('item.plant IS NOT NULL')
            ->andWhere('plant.type=:plantType')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('consumableLocations', Inventory::CONSUMABLE_LOCATIONS)
            ->setParameter('plantType', $type)
            ->getQuery()
            ->getResult()
        ;

        return $responseService->success($seeds, [ SerializationGroupEnum::MY_SEEDS ]);
    }

    /**
     * @Route("/updatePlantOrder", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updatePlantOrder(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $greenhouse = $user->getGreenhouse();

        if($greenhouse === null)
            throw new AccessDeniedHttpException('You don\'t have a greenhouse!');

        $plantIds = $request->request->get('order');

        if(!is_array($plantIds))
            throw new UnprocessableEntityHttpException('Must provide a list of plant ids, in the order you wish to save them in.');

        $allPlants = $user->getGreenhousePlants();

        $plantIds = array_filter($plantIds, fn(int $i) =>
            ArrayFunctions::any($allPlants, fn(GreenhousePlant $p) => $p->getId() === $i)
        );

        if(count($allPlants) !== count($plantIds))
            throw new UnprocessableEntityHttpException('The list of plants must include the full list of your plants; no more; no less!');

        foreach($allPlants as $plant)
        {
            $ordinal = array_search($plant->getId(), $plantIds) + 1;
            $plant->setOrdinal($ordinal);
        }

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/plantSeed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function plantSeed(
        ResponseService $responseService, InventoryRepository $inventoryRepository, Request $request,
        EntityManagerInterface $em, GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();
        $greenhouse = $user->getGreenhouse();

        if($greenhouse === null)
            throw new AccessDeniedHttpException('You don\'t have a greenhouse!');

        $seedId = $request->request->getInt('seed', 0);

        if($seedId <= 0)
            throw new UnprocessableEntityHttpException('"seed" is missing, or invalid.');

        $seed = $inventoryRepository->findOneBy([
            'id' => $seedId,
            'owner' => $user->getId(),
            'location' => Inventory::CONSUMABLE_LOCATIONS,
        ]);

        if($seed === null || $seed->getItem()->getPlant() === null)
            throw new NotFoundHttpException('There is no such seed. That\'s super-weird. Can you reload and try again?');

        $largestOrdinal = ArrayFunctions::max($user->getGreenhousePlants(), fn(GreenhousePlant $gp) => $gp->getOrdinal());
        $lastOrdinal = $largestOrdinal === null ? 1 : ($largestOrdinal->getOrdinal() + 1);

        $plantsOfSameType = $user->getGreenhousePlants()->filter(fn(GreenhousePlant $plant) =>
            $plant->getPlant()->getType() === $seed->getItem()->getPlant()->getType()
        );

        switch($seed->getItem()->getPlant()->getType())
        {
            case PlantTypeEnum::EARTH: $numberOfPlots = $greenhouse->getMaxPlants(); break;
            case PlantTypeEnum::WATER: $numberOfPlots = $greenhouse->getMaxWaterPlants(); break;
            case PlantTypeEnum::DARK: $numberOfPlots = $greenhouse->getMaxDarkPlants(); break;
            default: throw new \Exception('Selected item doesn\'t have a valid plant type! Someone let Ben know he messed up!');
        }

        if(count($plantsOfSameType) >= $numberOfPlots)
            throw new UnprocessableEntityHttpException('You can\'t plant anymore plants of this type.');

        $plant = (new GreenhousePlant())
            ->setOwner($user)
            ->setPlant($seed->getItem()->getPlant())
            ->setOrdinal($lastOrdinal + 1)
        ;

        $em->persist($plant);
        $em->remove($seed);
        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }
}
