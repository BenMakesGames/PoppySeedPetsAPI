<?php
namespace App\Controller;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetHouseTime;
use App\Entity\PetSkills;
use App\Entity\PlantYieldItem;
use App\Enum\BirdBathBirdEnum;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PlantTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\DateFunctions;
use App\Model\ItemQuantity;
use App\Repository\GreenhousePlantRepository;
use App\Repository\InventoryRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetActivity\GreenhouseAdventureService;
use App\Service\PetFactory;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/greenhouse")
 */
class GreenhouseController extends PoppySeedPetsController
{
    public const FORBIDDEN_COMPOST = [
        'Small Bag of Fertilizer',
        'Bag of Fertilizer',
        'Large Bag of Fertilizer'
    ];

    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getGreenhouse(
        ResponseService $responseService, GreenhousePlantRepository $greenhousePlantRepository,
        InventoryRepository $inventoryRepository, UserQuestRepository $userQuestRepository, EntityManagerInterface $em,
        NormalizerInterface $normalizer
    )
    {
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new AccessDeniedHttpException('You haven\'t purchased a Greenhouse plot yet.');

        $weeds = $userQuestRepository->findOrCreate($user, 'Greenhouse Weeds', (new \DateTimeImmutable())->modify('+8 hours')->format('Y-m-d H:i:s'));

        $weedTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $weeds->getValue());

        if($weedTime > new \DateTimeImmutable())
            $weedText = null;
        else
        {
            $weedText = ArrayFunctions::pick_one([
                'Don\'t need \'em; don\'t want \'em!',
                'Get outta\' here, weeds!',
                'Weeds can gtfo!',
                'WEEEEEEDS!! *shakes fist*',
                'Exterminate! EXTERMINATE!',
                'Destroy all weeds!',
            ]);
        }

        if(!$weeds->getId())
            $em->flush();

        $fertilizers = $inventoryRepository->findFertilizers($user);

        return $responseService->success(
            [
                'greenhouse' => $user->getGreenhouse(),
                'weeds' => $weedText,
                'plants' => $greenhousePlantRepository->findBy([ 'owner' => $user->getId() ]),
                'fertilizer' => $normalizer->normalize($fertilizers, null, [ 'groups' => [ SerializationGroupEnum::GREENHOUSE_FERTILIZER ] ]),
            ],
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE ]
        );
    }

    /**
     * @Route("/weed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function weedPlants(
        ResponseService $responseService, UserQuestRepository $userQuestRepository, EntityManagerInterface $em,
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        $weeds = $userQuestRepository->findOrCreate($user, 'Greenhouse Weeds', (new \DateTimeImmutable())->modify('+8 hours')->format('Y-m-d H:i:s'));

        $weedTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $weeds->getValue());

        if($weedTime > new \DateTimeImmutable())
            throw new UnprocessableEntityHttpException('Your garden\'s doin\' just fine right now, weed-wise.');

        $weeds->setValue((new \DateTimeImmutable())->modify('+18 hours')->format('Y-m-d H:i:s'));

        if(mt_rand(1, 4) === 1)
            $itemName = ArrayFunctions::pick_one([ 'Fluff', 'Red Clover', 'Talon', 'Feathers' ]);
        else
            $itemName = ArrayFunctions::pick_one([ 'Fluff', 'Crooked Stick', 'Crooked Stick' ]);

        $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this while weeding their Greenhouse.', LocationEnum::HOME);

        $em->flush();

        return $responseService->success($itemName);
    }

    /**
     * @Route("/talkToVisitingBird", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function talkToBird(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new AccessDeniedHttpException('You haven\'t purchased a Greenhouse plot yet.');

        if(!$user->getGreenhouse()->getVisitingBird())
            throw new NotFoundHttpException('Hm... there\'s no bird here. Reload, maybe??');

        switch($user->getGreenhouse()->getVisitingBird())
        {
            case BirdBathBirdEnum::OWL:
                $scroll = ArrayFunctions::pick_one([
                    'Behatting Scroll',
                    'Behatting Scroll',
                    'Behatting Scroll',
                    'Renaming Scroll',
                    'Renaming Scroll',
                    'Forgetting Scroll',
                ]);

                $inventoryService->receiveItem($scroll, $user, $user, 'Left behind by a huge owl that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the owl, it tilts its head at you. You freeze, and stare at each other for a few seconds before the owl flies off, dropping some kind of scroll as it goes!';
                break;

            case BirdBathBirdEnum::RAVEN:
                $inventoryService->receiveItem('Black Feathers', $user, $user, 'Left behind by a huge raven that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the raven, it turns to face you. You freeze, and stare at each other for a few seconds before the raven flies off in a flurry of Black Feathers!';
                break;

            case BirdBathBirdEnum::TOUCAN:
                $inventoryService->receiveItem('Cereal Box', $user, $user, 'Left behind by a huge toucan that visited ' . $user->getName() . '\'s Bird Bath.', LocationEnum::HOME);
                $message = 'As you approach the toucan, it turns to face you. You freeze, and stare at each other for a few seconds before the toucan flies off, leaving a Cereal Box behind.';
                break;

            default:
                throw new \Exception('Ben has done something wrong, and not accounted for this type of bird!');
        }

        $user->getGreenhouse()->setVisitingBird(null);

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
        InventoryService $inventoryService, EntityManagerInterface $em, UserStatsRepository $userStatsRepository
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
        {
            $totalFertilizer += $item->getItem()->getFertilizer();
            $em->remove($item);
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_COMPOSTED, count($items));

        $largeBags = (int)($totalFertilizer / 20);

        $totalFertilizer -= $largeBags * 20;

        $mediumBags = (int)($totalFertilizer / 15);

        $totalFertilizer -= $mediumBags * 15;

        $smallBags = (int)($totalFertilizer / 10);

        $totalFertilizer -= $smallBags * 10;

        $user->getGreenhouse()->setComposterFood($totalFertilizer);

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

        if(count($got) > 0)
            $responseService->addFlashMessage('You got ' . ArrayFunctions::list_nice($got) . '!');
        else
            $responseService->addFlashMessage('That wasn\'t quite enough to make a bag of fertilizer... but it\'s progress!');

        return $responseService->success();
    }

    /**
     * @Route("/{plant}/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvestPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, UserStatsRepository $userStatsRepository, PetRepository $petRepository,
        PetSpeciesRepository $petSpeciesRepository, MeritRepository $meritRepository,
        UserQuestRepository $userQuestRepository, GreenhouseAdventureService $greenhouseAdventureService,
        PetFactory $petFactory
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new UnprocessableEntityHttpException('This plant is not yet ready to harvest.');

        if(!$plant->getIsAdult() || $plant->getProgress() < 1)
            throw new UnprocessableEntityHttpException('This plant is not yet ready to harvest.');

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

        $plant->clearGrowth();

        if($plant->getPlant()->getName() === 'Tomato Plant' && DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
        {
            $message = 'You harvested-- WHOA, WAIT, WHAT?! It\'s a living tomato!?';

            $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

            $colorA = ColorFunctions::tweakColor(ArrayFunctions::pick_one([
                'FF6622', 'FFCC22', '77FF22', 'FF2222', '7722FF'
            ]));

            $colorB = ColorFunctions::tweakColor(ArrayFunctions::pick_one([
                '007700', '009922', '00bb44'
            ]));

            $tomateName = ArrayFunctions::pick_one([
                'Alicante', 'Azoychka', 'Krim', 'Brandywine', 'Campari', 'Canario', 'Tomkin',
                'Flamenco', 'Giulietta', 'Grandero', 'Trifele', 'Jubilee', 'Juliet', 'Kumato',
                'Monterosa', 'Montserrat', 'Plum', 'Raf', 'Roma', 'Rutgers', 'Marzano', 'Cherry',
                'Nebula', 'Santorini', 'Tomaccio', 'Tamatie', 'Tamaatar', 'Matomatisi', 'Yaanyo',
                'Pomidor', 'Utamatisi'
            ]);

            $species = $petSpeciesRepository->findOneBy([ 'name' => 'Tomate' ]);

            $tomate = $petFactory->createPet($user, $tomateName, $species, $colorA, $colorB, FlavorEnum::getRandomValue(), $meritRepository->getRandomStartingMerit());

            $tomate
                ->addMerit($meritRepository->findOneByName(MeritEnum::MOON_BOUND))
                ->setFoodAndSafety(mt_rand(10, 12), -9)
                ->setScale(mt_rand(80, 120))
            ;

            $em->remove($plant);

            if($numberOfPetsAtHome >= $user->getMaxPets())
            {
                $message .= "\n\n" . 'Seeing no space in your house, the creature wanders off to Daycare.';
                $tomate->setInDaycare(true);
            }
            else
            {
                $message .= "\n\n" . 'The creature wastes no time in setting up residence in your house.';
                $tomate->setInDaycare(false);
            }
        }
        else
        {
            $lootList = [];

            foreach($plant->getPlant()->getPlantYields() as $yield)
            {
                $quantity = mt_rand($yield->getMin(), $yield->getMax());

                for($i = 0; $i < $quantity; $i++)
                {
                    /** @var PlantYieldItem $loot */
                    $loot = ArrayFunctions::pick_one_weighted($yield->getItems(), function(PlantYieldItem $yieldItem) {
                        return $yieldItem->getPercentChance();
                    });

                    $lootItem = $loot->getItem();
                    $lootItemName = $lootItem->getName();

                    $inventoryService->receiveItem($lootItem, $user, $user, $user->getName() . ' grew this in their greenhouse.', LocationEnum::HOME);

                    if(array_key_exists($lootItemName, $lootList))
                        $lootList[$lootItemName]++;
                    else
                        $lootList[$lootItemName] = 1;
                }
            }

            $message = 'You harvested ' . ArrayFunctions::list_nice_quantities($lootList) . '!';
        }


        $plantsHarvested = $userStatsRepository->incrementStat($user, UserStatEnum::HARVESTED_PLANT);

        if($plantsHarvested->getValue() === 3)
        {
            $user->getGreenhouse()->increaseMaxPlants(3);
            $message .= ' And you\'ve been given three additional plots in the Greenhouse!';
        }
        else if(mt_rand(1, 3) === 1)
        {
            $petsAtHome = $petRepository->findBy([
                'owner' => $user->getId(),
                'inDaycare' => false
            ]);

            if(count($petsAtHome) > 0)
            {
                $greenhouseAdventureService->adventure(ArrayFunctions::pick_one($petsAtHome), $plant);
            }
        }

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->success();
    }

    /**
     * @Route("/{plant}/fertilize", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function fertilizePlant(
        GreenhousePlant $plant, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository
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

        return $responseService->success();
    }

    /**
     * @Route("/{plant}/pullUp", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pullUpPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em,
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
            if(mt_rand(1, 2) === 1)
                $inventoryService->receiveItem('Fluff', $user, $user, 'Dropped by a startled goat.', LocationEnum::HOME);
        }

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
     * @Route("/plantSeed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function plantSeed(
        ResponseService $responseService, InventoryRepository $inventoryRepository, Request $request,
        EntityManagerInterface $em
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

        $plantsOfSameType = $user->getGreenhousePlants()->filter(function(GreenhousePlant $plant) use($seed) {
            return $plant->getPlant()->getType() === $seed->getItem()->getPlant()->getType();
        });

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
        ;

        $em->persist($plant);
        $em->remove($seed);
        $em->flush();

        return $responseService->success();
    }
}
