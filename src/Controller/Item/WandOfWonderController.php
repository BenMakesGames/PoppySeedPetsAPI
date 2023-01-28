<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\EquipmentFunctions;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRepository;
use App\Repository\SpiceRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/item/wandOfWonder")
 */
class WandOfWonderController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/point", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function pointWandOfWonder(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em, InventoryService $inventoryService, PetRepository $petRepository,
        TransactionService $transactionService, ItemRepository $itemRepository, MeritRepository $meritRepository,
        PetExperienceService $petExperienceService, SpiceRepository $spiceRepository, Squirrel3 $squirrel3,
        StatusEffectService $statusEffectService, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->validateInventory($inventory, 'wandOfWonder/#/point');

        $user = $this->getUser();
        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        /** @var Pet[] $petsAtHome */
        $petsAtHome = $petRepository->findBy([
            'owner' => $user,
            'location' => PetLocationEnum::HOME
        ]);

        /** @var Pet|null $randomPet */
        $randomPet = $squirrel3->rngNextFromArray($petsAtHome);

        $expandedGreenhouseWithWand = $userQuestRepository->findOrCreate($user, 'Expanded Greenhouse With Wand of Wonder', false);

        $itemActionDescription = null;

        $possibleEffects = [
            'song',
            'featherStorm',
            'butterflies',
            'oneMoney',
            'yellowDye',
            'wine',
            'secretSeashell',
            'pb&j',
            'inspiring',
            'redUmbrella',
            'lightningInABottle',
            'wondrousStat',
            'tentacleAttack',
            'grantInvisibility',
            'spicedFish',
            'maxSize',
            'minSize',
            'metals',
            'wallOfMixedNutBrittle',
            'intoxication',
            'bubblegum',
            'oilUp',
            'giveRandomTool',
            'antiGrav',
        ];

        if($user->getGreenhouse() && !$expandedGreenhouseWithWand->getValue())
            $possibleEffects[] = 'expandGreenhouse';

        $effect = $squirrel3->rngNextFromArray($possibleEffects);

        $wandBroke = $squirrel3->rngNextInt(1, 2) === 1;

        switch($effect)
        {
            case 'song':
                $wandBroke = $wandBroke || ($squirrel3->rngNextInt(1, 2) === 1);
                $notes = $squirrel3->rngNextInt(6, 10);

                if($randomPet)
                {
                    $whose = $randomPet->getName() . '\'s';
                    $itemComment = "These Music Notes fell out of {$randomPet->getName()}'s ears after a Wand of Wonder sang for a while.";
                }
                else
                {
                    $whose = 'your';
                    $itemComment = "These Music Notes fell out of {$user->getName()}'s ears after a Wand of Wonder sang for a while.";
                }

                $itemActionDescription = "The wand begins to sing.\n\nThen, it keeps singing.\n\nS-- still singi-- oh, wait, no, it's stoppe-- ah, never mind, just a pause.\n\nStiiiiiiiill going...\n\nOh, okay, it's stopped again. Is it for real this time?\n\nIt seems to be for real.\n\nYeah, okay, it's done.\n\nYou shake your head; $notes Music Notes fall out of $whose ears and clatter on the ground!\n\nFrickin' wand!";

                for($x = 0; $x < $notes; $x++)
                    $inventoryService->receiveItem('Music Note', $user, $user, $itemComment, $location, $lockedToOwner);

                break;

            case 'featherStorm':
                $wandBroke = $wandBroke || ($squirrel3->rngNextInt(1, 2) === 1);
                $feathers = $squirrel3->rngNextInt(8, 12);

                if($randomPet)
                    $itemActionDescription = 'Hundreds of Feathers stream from the wand, filling the room. You never knew Feathers could be so loud! Moments later they begin to escape through crevices in the wall, but not before you and ' . $randomPet->getName() . ' grab a few!';
                else
                    $itemActionDescription = 'Hundreds of Feathers stream from the wand, filling the room. You never knew Feathers could be so loud! Moments later they begin to escape through crevices in the wall, but not before you grab a few!';

                for($x = 0; $x < $feathers; $x++)
                    $inventoryService->receiveItem('Feathers', $user, $user, 'A Wand of Wonder summoned these Feathers.', $location, $lockedToOwner);

                break;

            case 'butterflies':
                $wandBroke = false;
                $itemActionDescription = 'Hundreds of butterflies stream from the wand, filling the room. You never knew butterflies could be so loud! Moments later they escape through crevices in the wall, leaving no trace.';
                break;

            case 'expandGreenhouse':
                $itemActionDescription = 'You hear the earth shift in your Greenhouse! WHAT COULD IT MEAN!?!?';
                $user->getGreenhouse()->increaseMaxPlants(1);
                $expandedGreenhouseWithWand->setValue(true);
                break;

            case 'oneMoney':
                $wandBroke = false;

                if($randomPet)
                    $itemActionDescription = 'The wand begins to glow and shake violently. You and ' . $randomPet->getName() . ' hold on with all your might until, at last, it spits out a single ~~m~~. (Lame!)';
                else
                    $itemActionDescription = 'The wand begins to glow and shake violently. You hold on with all your might until, at last, it spits out a single ~~m~~. (Lame!)';

                $transactionService->getMoney($user, 1, 'Anticlimactically discharged by a Wand of Wonder.');
                break;

            case 'yellowDye':
                $wandBroke = $wandBroke || ($squirrel3->rngNextInt(1, 2) === 1);
                $dye = $squirrel3->rngNextInt(4, $squirrel3->rngNextInt(6, 10));
                $itemActionDescription = "Is that-- oh god! The wand is peeing!?\n\nWait, no... it's... Yellow Dye??!\n\nYou find some small jars to catch the stuff; in the end, you get " . $dye . " Yellow Dye.";

                for($x = 0; $x < $dye; $x++)
                    $inventoryService->receiveItem('Yellow Dye', $user, $user, 'A Wand of Wonder, uh, _summoned_ this Yellow Dye.', $location, $lockedToOwner);

                break;

            case 'wine':
                $wandBroke = $wandBroke || ($squirrel3->rngNextInt(1, 2) === 1);
                $wine = $squirrel3->rngNextInt(5, 10);
                $wines = [ 'Blackberry Wine', 'Blackberry Wine', 'Blueberry Wine', 'Blueberry Wine', 'Red Wine', 'Red Wine', 'Blood Wine' ];

                $itemActionDescription = "The wand shakes slightly, then begins pouring out wines of various colors! You grab some glasses, and catch as much as you can...";

                for($x = 0; $x < $wine; $x++)
                    $inventoryService->receiveItem($squirrel3->rngNextFromArray($wines), $user, $user, $user->getName() . ' caught this wine pouring out of a Wand of Wonder.', $location, $lockedToOwner);

                break;

            case 'secretSeashell':
                $wandBroke = $wandBroke || ($squirrel3->rngNextInt(1, 2) === 1);
                if($randomPet)
                {
                    $itemActionDescription = 'For a moment, you hear the sound of the ocean. ' . $randomPet->getName() . ' leans in to listen, and a Secret Seashell drops off of their head!';
                    $inventoryService->receiveItem('Secret Seashell', $user, $user, 'This fell off of ' . $randomPet->getName() . '\'s head after listening to a Wand of Wonder make ocean sounds.', $location, $lockedToOwner);
                }
                else
                {
                    $itemActionDescription = 'For a moment, you hear the sound of the ocean. You lean in to listen, and a Secret Seashell drops off of your head!';
                    $inventoryService->receiveItem('Secret Seashell', $user, $user, 'This fell off of ' . $user->getName() . '\'s head after listening to a Wand of Wonder make ocean sounds.', $location, $lockedToOwner);
                }

                break;

            case 'pb&j':
                $itemActionDescription = 'The wand turns into a PB&J, causing you to drop it. Upon impacting the floor, nuts and fruit spill everywhere!';

                $numItems = $squirrel3->rngNextInt(4, 6);

                $pbjItems = [
                    'Mixed Nuts', 'Mixed Nuts', 'Mixed Nuts', 'Mixed Nuts', 'Mixed Nuts', 'Sugar', 'Sugar',
                    'Apricot', 'Blackberries', 'Blueberries', 'Naner', 'Orange', 'Red', 'Pamplemousse'
                ];

                $inventoryService->receiveItem('Mixed Nuts', $user, $user, 'This spilled out of a Wand of Wonder after it turned into a PB&J!', $location, $lockedToOwner);
                $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Red', 'Orange', 'Naner' ]), $user, $user, 'This spilled out of a Wand of Wonder after it turned into a PB&J!', $location, $lockedToOwner);

                for($x = 0; $x < $numItems; $x++)
                    $inventoryService->receiveItem($squirrel3->rngNextFromArray($pbjItems), $user, $user, 'This spilled out of a Wand of Wonder after it turned into a PB&J!', $location, $lockedToOwner);

                break;

            case 'inspiring':
                if(count($petsAtHome) === 0)
                {
                    $itemActionDescription = 'The Wand of Wonder gave a very inspiring speech, but there weren\'t any pets around to listen...';
                }
                else
                {
                    $petNames = [];

                    foreach($petsAtHome as $pet)
                    {
                        $petNames[] = $pet->getName();
                        $statusEffectService->applyStatusEffect($pet, StatusEffectEnum::INSPIRED, 8 * 60);
                    }

                    $itemActionDescription = 'The Wand of Wonder gave a very inspiring speech. ' . ArrayFunctions::list_nice($petNames) . ' listened, enraptured.';

                    $responseService->setReloadPets();
                }

                break;

            case 'redUmbrella':
                $itemActionDescription = 'The wand straightens a bit, and, with a pop, an umbrella appears from one end!';
                break;

            case 'lightningInABottle':
                if($randomPet)
                {
                    $itemActionDescription = 'A burst of lightning nearly strikes ' . $randomPet->getName() . ', missing by less than a meter! After the dust clears, you see a bottle of lightning sitting where the lightning struck.';

                    $changes = new PetChanges($randomPet);

                    $randomPet->increaseSafety(-$squirrel3->rngNextInt(4, 8));
                    $petExperienceService->gainExp($randomPet, 1, [ PetSkillEnum::BRAWL ]);

                    $responseService->createActivityLog($randomPet, '%pet:' . $randomPet->getId() . '.name% barely dodged a blast of lightning from a Wand of Wonder!', '', $changes->compare($randomPet));
                }
                else
                {
                    $itemActionDescription = 'A burst of lightning nearly strikes you, missing by less than a meter! After the dust clears, you see a bottle of lightning sitting where the lightning struck.';
                }
                $inventoryService->receiveItem('Lightning in a Bottle', $user, $user, 'This was created by a Wand of Wonder!', $location, $lockedToOwner);
                break;

            case 'wondrousStat':
                if($randomPet)
                {
                    $randomMerit = $squirrel3->rngNextFromArray([
                        MeritEnum::WONDROUS_STRENGTH,
                        MeritEnum::WONDROUS_STAMINA,
                        MeritEnum::WONDROUS_DEXTERITY,
                        MeritEnum::WONDROUS_PERCEPTION,
                        MeritEnum::WONDROUS_INTELLIGENCE,
                    ]);

                    $merit = $meritRepository->findOneByName($randomMerit);

                    if($randomPet->hasMerit($randomMerit))
                    {
                        $leaves = $squirrel3->rngNextFromArray([
                            'melts away',
                            'evaporates',
                            'dissipates',
                            'vanishes'
                        ]);

                        $itemActionDescription = 'The wand bulges slightly... ' . $randomPet->getName() . '\'s ' . $randomMerit . ' ' . $leaves . '!';
                        $randomPet->removeMerit($merit);
                    }
                    else
                    {
                        $randomPet->addMerit($merit);
                        $itemActionDescription = 'The wand bulges slightly... ' . $randomPet->getName() . ' has been blessed with ' . $randomMerit . '!';
                    }
                }
                else
                {
                    $itemActionDescription = 'The wand bulges slightly, but quickly returns to its normal size. (Perhaps it would have been more effective with a pet around?)';
                }
                break;

            case 'tentacleAttack':
                if($randomPet)
                {
                    $itemActionDescription = 'A large tentacle bursts through the window and tries to slap the wand out of your hand, but ' . $randomPet->getName() . ' jumps in to defend you, tearing into the tentacle, which *poof*s into a bunch of smaller (and, thankfully, lifeless) Tentacles!';

                    $changes = new PetChanges($randomPet);

                    $petExperienceService->gainExp($randomPet, 2, [ PetSkillEnum::BRAWL ]);
                    $randomPet->increaseEsteem($squirrel3->rngNextInt(4, 8));

                    $responseService->createActivityLog($randomPet, '%pet:' . $randomPet->getId() . '.name% defeated a giant tentacle that attacked %user:' . $user->getId() . '.name%.', '', $changes->compare($randomPet));

                    $responseService->setReloadPets();

                    for($i = 0; $i < 3; $i++)
                        $inventoryService->receiveItem('Tentacle', $user, $user, 'A way-larger tentacle tried to attack you, but ' . $randomPet->getName() . ' defended you, and... then somehow the large tentacle went *poof*, and this smaller Tentacle was left behind, along with a couple others? I dunno, man. It was weird.', $location, $lockedToOwner);
                }
                else
                {
                    $itemActionDescription = 'A large tentacle bursts through the window and slaps the wand out of your hand! (If only a pet had been around!)';
                }
                break;

            case 'grantInvisibility':
                if($randomPet)
                {
                    $itemActionDescription = 'The wand squeaks like a balloon slowly losing air. When it finally finishes, you realize ' . $randomPet->getName() . ' has turned invisible!';
                    $responseService->setReloadPets();
                    $statusEffectService->applyStatusEffect($randomPet, StatusEffectEnum::INVISIBLE, 6 * 60);
                }
                else
                {
                    $itemActionDescription = 'The wand seems to look around the room for a moment, as if looking for something (perhaps a pet?)';
                }
                break;

            case 'spicedFish':
                $numSpicesAvailable = $spiceRepository->count([]);

                // five random spices
                // TODO: is this efficient? it looks gross.
                $spices = [
                    $spiceRepository->findBy([], [], 1, $squirrel3->rngNextInt(0, $numSpicesAvailable - 1))[0],
                    $spiceRepository->findBy([], [], 1, $squirrel3->rngNextInt(0, $numSpicesAvailable - 1))[0],
                    $spiceRepository->findBy([], [], 1, $squirrel3->rngNextInt(0, $numSpicesAvailable - 1))[0],
                    $spiceRepository->findBy([], [], 1, $squirrel3->rngNextInt(0, $numSpicesAvailable - 1))[0],
                    $spiceRepository->findBy([], [], 1, $squirrel3->rngNextInt(0, $numSpicesAvailable - 1))[0],
                ];

                foreach($spices as $spice)
                {
                    $newItem = $inventoryService->receiveItem($squirrel3->rngNextFromArray([ 'Fish', 'Fish', 'Fermented Fish' ]), $user, $user, 'This was produced by ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
                    $newItem->setSpice($spice);
                }

                $itemActionDescription = 'The fabulous fraction of fecund fir fruits five fragments of flavorful... fish??';

                break;

            case 'maxSize':
                if($randomPet)
                {
                    if($randomPet->getScale() >= 150)
                    {
                        $itemActionDescription = 'The wand bulges slightly... and ' . $randomPet->getName() . '? Hm. Well, actually, it seems nothing happened to them!';
                    }
                    else
                    {
                        if($randomPet->getScale() < 140)
                            $itemActionDescription = 'The wand bulges slightly... and ' . $randomPet->getName() . ' grows dramatically!';
                        else
                            $itemActionDescription = 'The wand bulges slightly... and ' . $randomPet->getName() . ' grows a little!';

                        $randomPet->setScale(150);

                        $responseService->createActivityLog($randomPet, '%pet:' . $randomPet->getId() . '.name% was increased in size by the fickle whims of a Wand of Wonder.', '');

                        $responseService->setReloadPets();
                    }
                }
                else
                {
                    $itemActionDescription = 'The wand bulges slightly, but quickly returns to its normal size. (Perhaps it would have been more effective with a pet around?)';
                }

                break;

            case 'minSize':
                if($randomPet)
                {
                    if($randomPet->getScale() <= 50)
                    {
                        $itemActionDescription = 'The wand bulges slightly... and ' . $randomPet->getName() . '? Hm. Well, actually, it seems nothing happened to them!';
                    }
                    else
                    {
                        if($randomPet->getScale() > 60)
                            $itemActionDescription = 'The wand bulges slightly... and ' . $randomPet->getName() . ' shrinks dramatically!';
                        else
                            $itemActionDescription = 'The wand bulges slightly... and ' . $randomPet->getName() . ' shrinks a little!';

                        $randomPet->setScale(50);

                        $responseService->createActivityLog($randomPet, '%pet:' . $randomPet->getId() . '.name% was reduced in size by the fickle whims of a Wand of Wonder.', '');

                        $responseService->setReloadPets();
                    }
                }
                else
                {
                    $itemActionDescription = 'The wand bulges slightly, but quickly returns to its normal size. (Perhaps it would have been more effective with a pet around?)';
                }

                break;

            case 'metals':
                $loot = $squirrel3->rngNextSubsetFromArray([
                    'Iron Bar',
                    'Iron Bar',
                    'Iron Key',
                    'Iron Sword',
                    'Iron Tongs',
                    'Dumbbell',
                    'Flute',
                    'Saucepan',

                    'Silver Bar',
                    'Silver Bar',
                    'Silver Key',
                    'Silver Colander',

                    'Gold Bar',
                    'Gold Key',
                    'Gold Triangle',
                    'Gold Tuning Fork'
                ], $squirrel3->rngNextInt(4, 5));

                foreach($loot as $itemName)
                    $inventoryService->receiveItem($itemName, $user, $user, 'This was created by ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

                $itemActionDescription = 'The wand turns into a tightly-bound scroll as a small pile of treasure collects around your feet!';

                break;

            case 'wallOfMixedNutBrittle':
                if($randomPet)
                {
                    $itemActionDescription = 'A wall of Mixed Nut Brittle appears between you and ' . $randomPet->getName() . '! You work together breaking it down into manageable pieces (and occasionally taking a bite...)';

                    $numPieces = $squirrel3->rngNextInt(4, 5);

                    for($i = 0; $i < $numPieces; $i++)
                        $inventoryService->receiveItem('Mixed Nut Brittle', $user, $user, 'Summoned by ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

                    $changes = new PetChanges($randomPet);

                    $petExperienceService->gainAffection($randomPet, 1);
                    $randomPet->increaseFood(5);

                    $responseService->createActivityLog($randomPet, '', '', $changes->compare($randomPet))
                        ->addTags($petActivityLogTagRepository->findByNames([ 'Eating' ]))
                    ;
                }
                else
                {
                    $itemActionDescription = 'A wall of Mixed Nut Brittle appears before you! You spend some time tearing it down into manageable pieces (a little help would have been nice!)';

                    $numPieces = $squirrel3->rngNextInt(3, 4);

                    for($i = 0; $i < $numPieces; $i++)
                        $inventoryService->receiveItem('Mixed Nut Brittle', $user, $user, 'Summoned by ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);
                }

                break;

            case 'intoxication':
                $wandBroke = false;

                if($randomPet)
                {
                    if($randomPet->getPregnancy())
                    {
                        if($randomPet->hasStatusEffect(StatusEffectEnum::TIRED))
                            $itemActionDescription = $randomPet->getName() . ' is suddenly looking even more tired!';
                        else
                            $itemActionDescription = $randomPet->getName() . ' is suddenly looking tired...';

                        $statusEffectService->applyStatusEffect($randomPet, StatusEffectEnum::TIRED, 4 * 60);
                    }
                    else
                    {
                        if($randomPet->getAlcohol() > 0)
                            $itemActionDescription = $randomPet->getName() . ' appears to have become... even more intoxicated!';
                        else
                            $itemActionDescription = $randomPet->getName() . ' appears to have become... intoxicated.';

                        $randomPet->increaseAlcohol($squirrel3->rngNextInt(4, 8));
                    }
                }
                else
                {
                    $itemActionDescription = 'You feel woozy.';
                }

                break;

            case 'bubblegum':
                if($randomPet)
                {
                    $statusEffectService->applyStatusEffect($randomPet, StatusEffectEnum::BUBBLEGUMD, 60);

                    $itemActionDescription = 'Thick strands of bubblegum suddenly grow out of ' . $randomPet->getName() . '\'s extremities! (Gross!)';
                    $responseService->setReloadPets();
                }
                else
                {
                    $itemActionDescription = 'The wand bulges slightly, but quickly returns to its normal size. (Perhaps it would have been more effective with a pet around?)';
                }

                break;

            case 'oilUp':
                if($randomPet)
                {
                    $statusEffectService->applyStatusEffect($randomPet, StatusEffectEnum::OIL_COVERED, 60);

                    $itemActionDescription = 'Oil sprays out of the wand, covering ' . $randomPet->getName() . '!';
                    $responseService->setReloadPets();
                }
                else
                {
                    $itemActionDescription = 'The wand bulges slightly, but quickly returns to its normal size. (Perhaps it would have been more effective with a pet around?)';
                }

                break;

            case 'giveRandomTool':

                $item = $itemRepository->findOneByName($squirrel3->rngNextFromArray([
                    'Giant Turkey Leg',
                    '"Gold" Idol',
                    'Aubergine Scepter',
                    'Heartstone',
                    'Fluff Heart',
                    'Chocolate-covered Naner with Nuts',
                    'Magic Bean Milk',
                    'Egg',
                    'Ant Queen',
                    'Welcome Note'
                ]));

                $newInventory = $inventoryService->receiveItem($item, $user, $user, 'Created by ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $lockedToOwner);

                if($randomPet)
                {
                    if($randomPet->getTool())
                    {
                        $itemActionDescription = $randomPet->getName() . ' drops their ' . $randomPet->getTool()->getItem()->getName() . '; ' . $item->getNameWithArticle() . ' appears in its place!';

                        EquipmentFunctions::unequipPet($randomPet);
                    }
                    else
                        $itemActionDescription = $randomPet->getName() . ' dons ' . $item->getNameWithArticle() . '!';

                    $randomPet->setTool($newInventory);

                    // move it to the wardrobe
                    $newInventory->setLocation(LocationEnum::WARDROBE);
                }
                else
                {
                    $itemActionDescription = 'You accidentally drop the wand; you reach to grab it, but instead grab ' . $item->getNameWithArticle() . '!';

                    if(!$wandBroke)
                        $itemActionDescription .= ' The wand clatters on the ground.';
                }

                break;

            case 'antiGrav':
                if($randomPet)
                {
                    $statusEffectService->applyStatusEffect($randomPet, StatusEffectEnum::ANTI_GRAVD, $squirrel3->rngNextInt(12, 24) * 60);

                    $itemActionDescription = $randomPet->getName() . ' turns upside down, and begins to float. Fortunately, they stop after just a couple inches... but they\'re still upside down!';
                    $responseService->setReloadPets();
                }
                else
                {
                    $itemActionDescription = 'The wand bulges slightly, but quickly returns to its normal size. (Perhaps it would have been more effective with a pet around?)';
                }

                break;
        }

        $itemActionDescription .= "\n\n";

        $transformsInto = null;

        if($effect === 'metals')
        {
            $transformsInto = 'Minor Scroll of Riches';
            $addedComment = 'This was once a Wand of Wonder!';
        }
        else if($effect === 'redUmbrella')
        {
            $transformsInto = 'Red Umbrella';
            $addedComment = 'This was once a Wand of Wonder!';
        }
        else if($effect === 'pb&j')
        {
            $transformsInto = $squirrel3->rngNextFromArray([
                'Apricot PB&J',
                'Blackberry PB&J',
                'Blueberry PB&J',
                'Naner PB&J',
                'Orange PB&J',
                'Pamplemousse PB&J',
                'Red PB&J'
            ]);
            $addedComment = 'This was once a Wand of Wonder... (it\'s _probably_ safe to eat??)';
        }
        else if($wandBroke)
        {
            $remains = $squirrel3->rngNextInt(1, 4);

            if($remains === 1)
            {
                $itemActionDescription .= 'Then, the wand snaps in two and crumbles to dust! (Well, actually, it crumbles to Silica Grounds.)';
                $transformsInto = 'Silica Grounds';
                $addedComment = 'These Silica Grounds were once a Wand of Wonder. Now they\'re just Silica Grounds. (Sorry, I guess that was a little redundant...)';
            }
            else if($remains === 2)
            {
                $itemActionDescription .= 'Then, the wand burst into flames, and is reduced to Charcoal!';
                $transformsInto = 'Charcoal';
                $addedComment = 'The charred remains of a Wand of Wonder :|';
            }
            else // $remains 3 || 4
            {
                $itemActionDescription .= 'You feel the last bits of magic drain from the wand. It\'s now nothing more than a common, Crooked Stick...';
                $transformsInto = 'Crooked Stick';
                $addedComment = 'The mundane remains of a Wand of Wonder...';
            }
        }

        if($transformsInto)
        {
            $inventory
                ->changeItem($itemRepository->findOneByName($transformsInto))
                ->addComment($addedComment)
            ;

            $responseService->setReloadInventory();
        }

        $em->flush();

        return $responseService->itemActionSuccess($itemActionDescription, [ 'itemDeleted' => $transformsInto !== null ]);
    }
}
