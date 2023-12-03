<?php
namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Pet;
use App\Entity\PlantYieldItem;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PollinatorEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\GrammarFunctions;
use App\Repository\EnchantmentRepository;
use App\Repository\UserQuestRepository;
use App\Service\FieldGuideService;
use App\Service\GreenhouseService;
use App\Service\HattierService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetActivity\GreenhouseAdventureService;
use App\Service\PetActivity\NoetalaAdventureService;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/greenhouse")]
class HarvestPlantController extends AbstractController
{
    #[Route("/{plant}/harvest", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function harvestPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, UserStatsService $userStatsRepository,
        UserQuestRepository $userQuestRepository, GreenhouseAdventureService $greenhouseAdventureService,
        GreenhouseService $greenhouseService, IRandom $squirrel3, FieldGuideService $fieldGuideService,
        HattierService $hattierService, TransactionService $transactionService,
        NoetalaAdventureService $noetalaAdventureService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new PSPNotFoundException('That plant does not exist.');

        if(new \DateTimeImmutable() < $plant->getCanNextInteract())
            throw new PSPInvalidOperationException('This plant is not yet ready to harvest.');

        if(!$plant->getIsAdult() || $plant->getProgress() < 1)
            throw new PSPInvalidOperationException('This plant is not yet ready to harvest.');

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
            else if($plant->getPlant()->getName() === 'Midnight Arch')
            {
                if($noetalaAdventureService->fightNoetalasWing($user))
                {
                    $responseService->addFlashMessage('After leaving the portal, it closed with a snap!');
                    $em->remove($plant);
                }

                $em->flush();

                return $responseService->success(
                    $greenhouseService->getGreenhouseResponseData($user),
                    [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
                );
            }

            throw new PSPInvalidOperationException($plant->getPlant()->getName() . ' cannot be harvested!');
        }

        if($plant->getPlant()->getName() === 'Earth Tree')
        {
            $transactionService->getRecyclingPoints($user, 25, 'Tess gave you 25 recycling points for replanting the Earth Tree!', [ 'Greenhouse', 'Earth Day' ]);

            $responseService->addFlashMessage('You carry the tree out to where Tess is planting new trees on the island, and plant the Earth Tree. She gives you 25 recycling points for your help taking care of the Earth!');

            $leaves = EnchantmentRepository::findOneByName($em, 'Leafy');

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
            $vinesAura = EnchantmentRepository::findOneByName($em, 'of Wild Growth');

            $responseService->addFlashMessage('After harvesting the ' . $plant->getPlant()->getName() . ', an odd-looking fairy pops out and bestows a wreath of vines to you. "As you give life to the Earth, you give life to my people. Please, accept this gift for all you have done for us!" (A new style is available at the Hattier\'s! _And_ you somehow got 100 recycling points?! Sure; why not!)');

            $transactionService->getRecyclingPoints($user, 100, 'A fairy gave you 100 Recycling Points in thanks for growing so many plants!', [ 'Greenhouse', 'Fae-kind' ]);

            $hattierService->playerUnlockAura($user, $vinesAura, 'A fairy gave you this after you harvested your 1000th plant!');
        }
        else
        {
            $eligiblePets = $em->getRepository(Pet::class)->findBy([
                'owner' => $user->getId(),
                'location' => PetLocationEnum::HOME
            ]);

            if($user->getGreenhouse()->getHelper())
                $eligiblePets[] = $user->getGreenhouse()->getHelper();

            if(count($eligiblePets) > 0)
            {
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
}
