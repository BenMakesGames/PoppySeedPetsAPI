<?php
namespace App\Controller\Item;

use App\Entity\Fireplace;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Repository\InventoryRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/fairy")
 */
class HouseFairyController extends PoppySeedPetsItemController
{
    public const FAIRY_NAMES = [
        'Ævintýri', 'Alfrigg', 'Ant', 'Ao', 'Aphid', 'Apricot', 'Arethusa', 'Ariel',
        'Basil', 'Beeswax', 'Bitterweed', 'Blueberry', 'Bromine',
        'Cardamom', 'Celadon', 'Celeste', 'Cobweb', 'Coriander', 'Cornsilk', 'Cottonweed', 'Cottonwood', 'Crysta', 'Curium', 'Cyclamen',
        'Donella', 'Drake', 'Durin',
        'Ecru', 'Elwood', 'Erline',
        'Faye', 'Faylinn', 'Fée', 'Fern', 'Fishbone', 'Fluorine', 'Fran', 'Frostbite',
        'Gallium', 'Ginger', 'Goldenrod', 'Granite', 'Gumweed',
        'Harp', 'Holly', 'Hollywood',
        'Inchworm', 'Incus', 'Iolanthe', 'Iris',
        'Kailen', 'Klabautermann',
        'Lance', 'Lilac', 'Lily', 'Lumina',
        'Malachite', 'Marigold', 'Marrowfat', 'Melon', 'Milkweed', 'Mint', 'Moss', 'Moth', 'Mulberry', 'Mustardseed',
        'Navi', 'Neráida', 'Nissa',
        'Odelina', 'Onyx', 'Orin', 'Oxblood',
        'Paprika', 'Peri', 'Peaseblossom', 'Plum', 'Potato', 'Pudding', 'Pumpkin',
        'Rhythm', 'Ribbon', 'Riverweed', 'Robin', 'Rockweed', 'Roosevelt', 'Rosewood', 'Rust',
        'Sage', 'Saria', 'Scarlet', 'Seashell', 'Seaweed', 'Sebille', 'Sinopia', 'Sunset',
        'Tania', 'Tapioca', 'Tatiana', 'Tetra', 'Thistle', 'Tibia', 'Timberwolf', 'Tin', 'Tumbleweed',
        'Uranium',
        'Volt',
        'Warren', 'Waxweed', 'Whalebone', 'Wintersnap',
        'Yak',
        'Zanna', 'Zoe',
    ];

    private function fairyName(Inventory $i)
    {
        return self::FAIRY_NAMES[$i->getId() % count(self::FAIRY_NAMES)];
    }

    /**
     * @Route("/{inventory}/hello", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function sayHello(
        Inventory $inventory, ResponseService $responseService, UserQuestRepository $userQuestRepository,
        EntityManagerInterface $em
    )
    {
        $this->validateInventory($inventory, 'fairy/#/hello');

        $user = $this->getUser();

        $saidHello = $userQuestRepository->findOrCreate($user, 'Said Hello to House Fairy', false);

        if(!$saidHello->getValue())
        {
            $saidHello->setValue(true);
            $em->flush();

            return $responseService->itemActionSuccess(
                '"Hi! Thanks for saving me!" It says. "The human world is _crazy!_ Your place is kind of nice, though. Mind if I stick around for a little while? Maybe I can help you out. I am a House Fairy, after all. By the way, my name\'s ' . $this->fairyName($inventory) . '!"'
            );
        }
        else
        {
            $pet = $inventory->getHolder();

            if($pet !== null)
            {
                $adjectives = [
                    'is pretty cute', 'is really friendly', 'actually smells really nice',
                    'seems to like listening to my stories', 'is really funny',
                    'has a charming aura', 'makes sure I don\'t get hurt', 'reminds me of Hahanu'
                ];

                $adjective = $adjectives[($inventory->getId() + $pet->getId()) % count($adjectives)];

                if($adjective === 'reminds me of Hahanu' && strtolower(str_replace(' ', '', $pet->getName())) === 'hahanu')
                    $adjective = 'reminds me of the real Hahanu';

                return $responseService->itemActionSuccess(
                    '"I mean, I wasn\'t exactly expecting to get carried around like this," says ' . $this->fairyName($inventory) . '. "But ' . $inventory->getHolder()->getName() .  ' ' . $adjective . ', so I guess it\'s fine?? Better than being carried around by that raccoon, anyway!"'
                );
            }
            else
            {
                return $responseService->itemActionSuccess(
                    '"Oh, hi!" says ' . $this->fairyName($inventory) . '.'
                );
            }
        }
    }

    /**
     * @Route("/{inventory}/buildFireplace", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buildBasement(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, InventoryRepository $inventoryRepository
    )
    {
        $this->validateInventory($inventory, 'fairy/#/buildFireplace');

        $user = $this->getUser();

        $quint = $inventoryRepository->findOneToConsume($user, 'Quintessence');

        if($user->getUnlockedFireplace() && $user->getFireplace())
        {
            return $responseService->itemActionSuccess(
                '"You already have a Fireplace, and it\'s already as fireplacey as a fireplace can be!" says ' . $this->fairyName($inventory) . '.' . "\n\n". 'Fairy nough-- er: fair enough.'
            );
        }
        else
        {
            if($quint === null)
            {
                return $responseService->itemActionSuccess(
                    '"I\'ll do it... for a _Quintessence!_" ' . $this->fairyName($inventory) . ' screams with excitement. (A scream which would have been annoying were the creature\'s scream not as tiny as the creature itself.)'
                );
            }

            $user->setUnlockedFireplace();

            $fireplace = (new Fireplace())->setUser($user);

            $em->persist($fireplace);

            $em->remove($quint);

            $em->flush();

            return $responseService->itemActionSuccess(
                '"Thanks! Oh, but actually: do you also have any Bricks?" asks ' . $this->fairyName($inventory) . '. "I\'m going to need about 20 exactly...' . "\n\n" . '"Hehehe! Your face! Humans are so cute! I\'m going to make the bricks - and everything else - out of Quintessence! Obviously! Now, let me just... do a little thing here..."' . "\n\n" . $this->fairyName($inventory) . ' wriggles a little, as if something is crawling around inside their... dress? Tunic? Whatever it is.' . "\n\n" . '"Alright! It\'s done!" announces the fairy with a grin.' . "\n\n" . 'And indeed: there\'s now a fireplace in the living room! (But you, dear Poppy Seed Pets player, can find it in the game menu.)',
                [ 'reloadInventory' => true ]
            );
        }
    }

    /**
     * @Route("/{inventory}/quintessence", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function askAboutQuintessence(
        Inventory $inventory, ResponseService $responseService
    )
    {
        $this->validateInventory($inventory, 'fairy/#/quintessence');

        return $responseService->itemActionSuccess(
            '"I mean, it\'s kind of like a currency in the Umbra. And a food," ' . $this->fairyName($inventory) . ' explains. "All magical creatures want it. Need it. If you\'re looking to get some, honestly, trading with a magical creature is one of the most reliable ways. Or you could beat up a ghost, werewolf, vampire, or some other awful creature like that."'
        );
    }
}