<?php
namespace App\Controller\Beehive;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\PetChanges;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/beehive")]
class HarvestController extends AbstractController
{
    #[Route("/harvest", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function harvest(
        ResponseService $responseService, EntityManagerInterface $em, InventoryService $inventoryService, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive) || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $beehive = $user->getBeehive();
        $itemNames = [];

        if($beehive->getRoyalJellyPercent() >= 1)
        {
            $beehive->setRoyalJellyProgress(0);

            $inventoryService->receiveItem('Royal Jelly', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);

            $itemNames[] = 'Royal Jelly';
        }

        if($beehive->getHoneycombPercent() >= 1)
        {
            $beehive->setHoneycombProgress(0);

            $inventoryService->receiveItem('Honeycomb', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);

            $itemNames[] = 'Honeycomb';
        }

        if($beehive->getMiscPercent() >= 1)
        {
            $beehive->setMiscProgress(0);

            $possibleItems = [
                'Crooked Stick', 'Fluff', 'Yellow Dye', 'Glue', 'Sugar', 'Sugar', 'Sugar', 'Antenna',
            ];

            $item = $rng->rngNextFromArray($possibleItems);

            $newItems = [
                $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME)
            ];

            if($beehive->getHelper())
            {
                $helper = $beehive->getHelper();
                $petWithSkills = $helper->getComputedSkills();

                $changes = new PetChanges($helper);

                if($helper->hasMerit(MeritEnum::GREEN_THUMB))
                {
                    $gathering = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();

                    $extraItem1 = PetAssistantService::getExtraItem($rng, $gathering,
                        [ 'Tea Leaves', 'Blueberries', 'Blackberries', 'Grandparoot', 'Orange', 'Red' ],
                        [ 'Onion', 'Paper', 'Naner', 'Iron Ore' ],
                        [ 'Gypsum', 'Mixed Nuts', 'Apricot', 'Silver Ore', ],
                        [ 'Gold Ore', 'Liquid-hot Magma' ]
                    );

                    $extraItem2 = PetAssistantService::getExtraItem($rng, $gathering,
                        [ 'Agrimony', 'Blueberries', 'Blackberries', 'Orange', 'Red' ],
                        [ 'Onion', 'Tomato', 'Naner', 'Sunflower' ],
                        [ 'Mint', 'Mixed Nuts', 'Apricot', 'Melowatern', ],
                        [ 'Goodberries', 'Iris' ]
                    );

                    $activityLog = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . '\'s bees while they were out gathering, and collected ' . $extraItem1 . ' AND ' . $extraItem2 . '.');

                    $inventoryService->petCollectsItem($extraItem1, $helper, $helper->getName() . ' helped ' . $user->getName() . '\'s bees gathered this.', $activityLog);
                    $inventoryService->petCollectsItem($extraItem2, $helper, $helper->getName() . ' helped ' . $user->getName() . '\'s bees gathered this.', $activityLog);
                }
                else
                {
                    $gathering = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();
                    $hunting = $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal();

                    $total = $gathering + $hunting;

                    if($total < 2)
                        $doGatherAction = $rng->rngNextBool();
                    else
                        $doGatherAction = $rng->rngNextInt(1, $total) <= $gathering;

                    if($doGatherAction)
                    {
                        $extraItem = PetAssistantService::getExtraItem($rng, $gathering,
                            [ 'Tea Leaves', 'Blueberries', 'Blackberries', 'Grandparoot', 'Orange', 'Red' ],
                            [ 'Onion', 'Paper', 'Naner', 'Iron Ore' ],
                            [ 'Gypsum', 'Mixed Nuts', 'Apricot', 'Silver Ore', ],
                            [ 'Gold Ore', 'Liquid-hot Magma' ],
                        );

                        $verb = 'gather';
                    }
                    else
                    {
                        $extraItem = PetAssistantService::getExtraItem($rng, $hunting,
                            [ 'Scales', 'Feathers', 'Egg' ],
                            [ 'Toadstool', 'Talon', 'Onion' ],
                            [ 'Toad Legs', 'Jar of Fireflies' ],
                            [ 'Silver Bar', 'Gold Bar', 'Quintessence' ],
                        );

                        $verb = 'hunt';
                    }

                    $activityLog = PetActivityLogFactory::createUnreadLog($em, $helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . '\'s bees while they were out ' . $verb . 'ing, and collected ' . $extraItem . '.');

                    $inventoryService->petCollectsItem($extraItem, $helper, $helper->getName() . ' helped ' . $user->getName() . '\'s bees ' . $verb . ' this.', $activityLog);
                }

                $activityLog
                    ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
                    ->setChanges($changes->compare($helper))
                    ->addTags(PetActivityLogTagHelpers::findByNames($em, [ 'Add-on Assistance', 'Beehive' ]))
                ;
            }

            foreach($newItems as $newItem)
            {
                if($newItem->getItem()->getName() === 'Crooked Stick' || $newItem->getItem()->getFood())
                {
                    if($rng->rngNextInt(1, 20) === 1)
                        $newItem->setSpice(SpiceRepository::findOneByName($em, 'of Queens'));
                    else
                        $newItem->setSpice(SpiceRepository::findOneByName($em, 'Anthophilan'));
                }

                $itemNames[] = $newItem->getFullItemName();
            }
        }

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        $responseService->addFlashMessage('You received ' . ArrayFunctions::list_nice($itemNames) . '.');

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
