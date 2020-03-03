<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\TotemPole;
use App\Entity\TotemPoleTotem;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/totem")
 */
class TotemController extends PoppySeedPetsItemController
{
    private const TOTEM_HEIGHTS = [
    ];

    /**
     * @Route("/{inventory}/add", methods={"POST"})
     */
    public function addToPole(Inventory $inventory, EntityManagerInterface $em, ResponseService $responseService)
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'totem/#/add');

        $message = null;

        if(!$user->getUnlockedTotemPoleGarden())
        {
            $user->setUnlockedTotemPoleGarden();

            $totemPole = (new TotemPole())
                ->setOwner($user)
            ;

            $em->persist($totemPole);

            $message = 'One small step... (the Totem Pole has been added to the menu!)';
        }

        $appearance = $inventory->getItem()->getImage();
        $totemPole = $user->getTotemPole();
        $oldCentimeters = $totemPole->getHeightInCentimeters();
        $oldKilometers = $totemPole->getHeightInKilometers();

        $totemPole
            ->increaseHeight()
            ->increaseHeightInCentimeters(self::TOTEM_HEIGHTS[$inventory->getItem()->getName()])
        ;

        $totem = (new TotemPoleTotem())
            ->setOwner($user)
            ->setAppearance($appearance)
            ->setOrdinal($totemPole->getHeight())
        ;

        $newCentimeters = $totemPole->getHeightInCentimeters();
        $newKilometers = $totemPole->getHeightInKilometers();

        if($oldCentimeters === 0 && $newCentimeters > 0)
        {
            $message = 'You placed your first totem! Nice! Visit the Totem Garden to collect a reward!';
            $totemPole->addRewardExtra('One Small Step...');
        }
        else if($oldCentimeters <= 272 && $newCentimeters > 272)
        {
            $message = 'Your totem pole... it\'s taller than Robert Wadlow!';
            $totemPole->addRewardExtra('Robert Wadlow');
        }
        else if($oldCentimeters <= 600 && $newCentimeters > 600)
        {
            // Giraffe = 6m
            $message = 'Your totem pole... it\'s taller than a giraffe!';
            $totemPole->addRewardExtra('Giraffalo are Smaller');
        }
        else if($oldCentimeters <= 1800 && $newCentimeters > 1800)
        {
            // Sauroposeidon = 18m
            $message = 'Your totem pole... it\'s taller than a Sauroposeidon! (btw, a "sauroposeidon" is a v tall dino.)';
            $totemPole->addRewardExtra('Sauroposeidon');
        }
        else if($oldCentimeters <= 2400 && $newCentimeters > 2400)
        {
            // Blue Whale = 24m
            $message = 'Your totem pole... it\'s taller than most blue whales are long!';
            $totemPole->addRewardExtra('Blue Whale');
        }
        else if($oldCentimeters <= 3700 && $newCentimeters > 3700)
        {
            // Lion's Mane Jellyfish = 37m
            $message = 'Your totem pole... it\'s taller than the longest-known lion\'s mane jellyfish!';
            $totemPole->addRewardExtra('Lion\'s Mane Jellyfish');
        }
        else if($oldCentimeters <= 6500 && $newCentimeters > 6500)
        {
            // Angkor Wat = 65m
            $message = 'Your totem pole... it\'s taller than the Angkor Wat!';
            $totemPole->addRewardExtra('Angkor Wat');
        }
        else if($oldCentimeters <= 9300 && $newCentimeters > 9300)
        {
            // Statue of Liberty = 93m
            $message = 'Your totem pole... it\'s taller than the Statue of Liberty!';
            $totemPole->addRewardExtra('Thanks, France!');
        }
        else if($oldCentimeters <= 11600 && $newCentimeters > 11600)
        {
            // Hyperion = 115m
            $message = 'Your totem pole... it\'s taller than the tallest Redwood!';
            $totemPole->addRewardExtra('Hyperion');
        }
        else if($oldCentimeters <= 14000 && $newCentimeters > 14000)
        {
            // Pyramids = 140m
            $message = 'Your totem pole... it\'s taller than the Great Pyramid!';
            $totemPole->addRewardExtra('Great Pyramid');
        }
        else if($oldCentimeters <= 32500 && $newCentimeters > 32500)
        {
            // Eiffel Tower = 325m
            $message = 'Your totem pole... it\'s taller than the Eiffel Tower!';
            $totemPole->addRewardExtra('Eiffel Tower');
        }
        else if($oldCentimeters <= 38100 && $newCentimeters > 38100)
        {
            // Empire State Building = 381m
            $message = 'Your totem pole... it\'s taller than the Empire State Building!';
            $totemPole->addRewardExtra('NY, NY');
        }
        else if($oldCentimeters <= 82800 && $newCentimeters > 82800)
        {
            // Burj Khalifa = 828m
            $message = 'Your totem pole... it\'s taller than the Burj Khalifa!';
            $totemPole->addRewardExtra('Burj Khalifa');
        }
        else if($oldCentimeters <= 97900 && $newCentimeters > 97900)
        {
            // Burj Khalifa = 979m
            $message = 'Your totem pole... it\'s taller than Angel Falls!';
            $totemPole->addRewardExtra('Angel Falls');
        }
        else if($oldCentimeters <= 128100 && $newCentimeters > 128100)
        {
            // Mount Vesuvius = 1,281m
            $message = 'Your totem pole... it\'s taller than Mount Vesuvius!';
            $totemPole->addRewardExtra('Mount Vesuvius');
        }
        else if($oldCentimeters <= 185700 && $newCentimeters > 185700)
        {
            // Grand Canyon = 1,857m
            $message = 'Your totem pole... it\'s taller than the Grand Canyon is deep!';
            $totemPole->addRewardExtra('Pretty Grand');
        }
        else if($oldCentimeters <= 327000 && $newCentimeters > 327000)
        {
            // Colca Canyon = 3,270m
            $message = 'Your totem pole... it\'s taller than the Colca Canyon is deep!';
            $totemPole->addRewardExtra('Peru\'s is Bigger');
        }
        else if($oldCentimeters <= 510000 && $newCentimeters > 510000)
        {
            // La Rinconada = 5,100m
            $message = 'Your totem pole... it\'s taller than the highest city!';
            $totemPole->addRewardExtra('La Rinconada');
        }
        else if($oldCentimeters <= 700000 && $newCentimeters > 700000)
        {
            // Valles Marineris = 7,000m
            $message = 'Your totem pole... it\'s taller than the Valles Marineris is deep!';
            $totemPole->addRewardExtra('Mars\' is Bigger');
        }
        else if($oldCentimeters <= 885000 && $newCentimeters > 885000)
        {
            // Mount Everest = 8,848m tall
            $message = 'Your totem pole... it\'s taller than Mount Everest!';
            $totemPole->addRewardExtra('Mount Everest');
        }
        else if($oldCentimeters <= 900000 && $newCentimeters > 900000)
        {
            // > 9000m
            $message = 'Your totem pole... IT\'S OVER 9000! ... meters!';
            $totemPole->addRewardExtra('IT\'S OVER 9000!');
        }
        else if($oldCentimeters <= 1098400 && $newCentimeters > 1098400)
        {
            // Mariana Trench = 10,984m
            $message = 'Your totem pole... it\'s taller than the Mariana Trench is deep!';
            $totemPole->addRewardExtra('Mariana Trench');
        }
        else if($oldKilometers < 12 && $oldKilometers >= 12)
        {
            // the Stratosphere starts at about 12km
            $message = 'Your totem pole... its reached the stratosphere!';
            $totemPole->addRewardExtra('Halfway to Oblivion');
        }
        else if($oldCentimeters <= 2122900 && $newCentimeters > 2122900)
        {
            // Olympus Mons = 21,229m (jesus, it's huge)
            $message = 'Your totem pole... it\'s taller than Olympus Mons!';
            $totemPole->addRewardExtra('Mars Wins Again');
        }
        else if($oldCentimeters <= 3466800 && $newCentimeters > 3466800)
        {
            // highest balloon flight = 34,668m
            $message = 'Your totem pole... its height surpasses the highest ever manned balloon flight!';
            $totemPole->addRewardExtra('USN Strato-Lab V');
        }
        else if($oldKilometers < 82 && $newKilometers >= 82)
        {
            $message = 'Your totem pole... it\'s taller than the Panama Canal is long!';
            $totemPole->addRewardExtra('A Man, A Plan, A Canal');
        }
        else if($oldKilometers < 410 && $newKilometers >= 410)
        {
            // ISS = 410,000m
            $message = 'Your totem pole... it surpasses the International Space Station!';
            $totemPole->addRewardExtra('ISS');
        }
        else if($oldKilometers < 1776 && $newKilometers >= 1776)
        {
            // Grand Canal = 1,776,000m
            $message = 'Your totem pole... it\'s taller than the Grand Canal is long!';
            $totemPole->addRewardExtra('Quite Grand');
        }
        /*
        else if($oldHeight <= 3578600000 && $newHeight > 3578600000)
        {
            // geostationary orbit = 35,786,000m
            $message = 'Your totem pole... it\'s top is in geostationary orbit! (That could come in handy!)';
            $totemPole->addRewardExtra('Geostationary');
        }*/

        // every 100m
        if(floor($oldCentimeters / 10000) != floor($newCentimeters / 10000))
        {
            if($message === null)
                $message = 'You added another 100m to your totem pole!';

            $totemPole->incrementReward100m();
        }

        if($message === null)
            $message = 'Your totem pole is now ' . round($newCentimeters / 100, 1) . 'm tall. One, tiny step closer...';
        else
            $message .= ' Visit the Totem Garden to collect a reward!';

        $em->persist($totem);

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
