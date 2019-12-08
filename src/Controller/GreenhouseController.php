<?php
namespace App\Controller;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetSkills;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
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
use App\Service\ResponseService;
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
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getGreenhouse(
        ResponseService $responseService, GreenhousePlantRepository $greenhousePlantRepository,
        InventoryRepository $inventoryRepository, UserQuestRepository $userQuestRepository, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedGreenhouse() === null)
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

        return $responseService->success(
            [
                'weeds' => $weedText,
                'plants' => $greenhousePlantRepository->findBy([ 'owner' => $user->getId() ]),
                'fertilizer' => $inventoryRepository->findFertilizers($user),
            ],
            [ SerializationGroupEnum::GREENHOUSE_PLANT ]
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
            $itemName = null;
        else
        {
            if(mt_rand(1, 3) === 1)
                $itemName = ArrayFunctions::pick_one([ 'Fluff', 'Fluff', 'Red Clover', 'Talon', 'Feathers' ]);
            else
                $itemName = 'Crooked Stick';

            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' found this while weeding their Greenhouse.', LocationEnum::HOME);
        }

        $em->flush();

        return $responseService->success($itemName);
    }

    /**
     * @Route("/{plant}/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvestPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, UserStatsRepository $userStatsRepository, PetRepository $petRepository,
        PetSpeciesRepository $petSpeciesRepository, MeritRepository $meritRepository
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new UnprocessableEntityHttpException('This plant is not yet ready to harvest.');

        if(!$plant->getIsAdult() || $plant->getProgress() < 1)
            throw new UnprocessableEntityHttpException('This plant is not yet ready to harvest.');

        $plant->clearGrowth();

        $yield = mt_rand($plant->getPlant()->getMinYield(), $plant->getPlant()->getMaxYield());

        if($plant->getPlant()->getItem()->getName() === 'Mysterious Seed')
        {
            $possibleYield = [
                'Celery', 'Sugar', 'Egg', 'Toad Legs', 'Crooked Stick',
                'Fluff', 'Aging Powder', 'Beans', 'Noodles', 'Algae',
                'Orange', 'Red', 'Mermaid Egg', 'Tentacle', 'Carrot',
                'Butter', 'Pectin', 'Plain Yogurt', 'Naner', 'Pie Crust'
            ];

            shuffle($possibleYield);

            $items = [];

            for($i = 0; $i < $yield; $i++)
            {
                $inventoryService->receiveItem($possibleYield[$i], $user, $user, $user->getName() . ' grew this in their greenhouse.', LocationEnum::HOME);
                $items[] = $possibleYield[$i];
            }

            $message = 'You harvested ' . ArrayFunctions::list_nice($items) . '!';
        }
        else
        {
            if($plant->getPlant()->getItem()->getName() === 'Tomato' && DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
            {
                $message = 'You harvested-- WHOA, WAIT, WHAT?! It\'s a living tomato!?';

                $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

                $tomateSkills = new PetSkills();

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

                $tomate = (new Pet())
                    ->setOwner($user)
                    ->setName($tomateName)
                    ->setSpecies($petSpeciesRepository->findOneBy([ 'name' => 'Tomate' ]))
                    ->setSkills($tomateSkills)
                    ->setColorA($colorA)
                    ->setColorB($colorB)
                    ->setFavoriteFlavor(FlavorEnum::getRandomValue())
                    ->setNeeds(mt_rand(10, 12), -9)
                    ->addMerit($meritRepository->findOneByName(MeritEnum::MOON_BOUND))
                ;

                $em->persist($tomateSkills);
                $em->persist($tomate);

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
                $quantity = new ItemQuantity();

                $quantity->item = $plant->getPlant()->getItem();
                $quantity->quantity = $yield;

                $inventoryService->giveInventory($quantity, $user, $user, $user->getName() . ' grew this in their greenhouse.', LocationEnum::HOME);

                $message = 'You harvested ' . $yield . '× ' . $plant->getPlant()->getItem()->getName() . '!';
            }
        }

        $plantsHarvested = $userStatsRepository->incrementStat($user, UserStatEnum::HARVESTED_PLANT);

        if($plantsHarvested->getValue() === 3)
            $user->increaseMaxPlants(3);

        $em->flush();

        $responseService->addActivityLog((new PetActivityLog())->setEntry($message));

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
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('That plant does not exist.');

        $em->remove($plant);
        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/seeds", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getSeeds(ResponseService $responseService, InventoryRepository $inventoryRepository)
    {
        $user = $this->getUser();

        $seeds = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.plant IS NOT NULL')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('consumableLocations', Inventory::CONSUMABLE_LOCATIONS)
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

        if(count($user->getGreenhousePlants()) >= $user->getMaxPlants())
            throw new UnprocessableEntityHttpException('You can\'t plant anymore plants.');

        $seedId = $request->request->getInt('seed', 0);

        if($seedId <= 0)
            throw new UnprocessableEntityHttpException('"seed" is missing, or invalid.');

        $item = $inventoryRepository->findOneBy([
            'id' => $seedId,
            'owner' => $user->getId(),
            'location' => Inventory::CONSUMABLE_LOCATIONS,
        ]);

        if($item === null || $item->getItem()->getPlant() === null)
            throw new NotFoundHttpException('There is no such seed. That\'s super-weird. Can you reload and try again?');

        $plant = (new GreenhousePlant())
            ->setOwner($user)
            ->setPlant($item->getItem()->getPlant())
        ;

        $em->persist($plant);
        $em->remove($item);
        $em->flush();

        return $responseService->success();
    }
}