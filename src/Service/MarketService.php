<?php
namespace App\Service;

use App\Entity\DailyMarketInventoryTransaction;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class MarketService
{
    private $em;
    private $userStatsRepository;

    public function __construct(EntityManagerInterface $em, UserStatsRepository  $userStatsRepository)
    {
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function getItemToRaiseLimit(User $user): ?array
    {
        switch($user->getMaxSellPrice())
        {
            case 10: return [ 'itemName' => 'String', 'hint' => 'Your pets should be able to make some out of Fluff.' ];
            case 20: return [ 'itemName' => 'Pectin', 'hint' => 'You should be able to extract some from a Red, or Carrot.' ];
            case 30: return [ 'itemName' => 'Iron Bar', 'hint' => 'Your pets will need to refine some from Iron Ore. When you do get an Iron Bar, please just leave it on the desk over there. Fairies don\'t do iron.' ];
            case 40: return [ 'itemName' => 'Elvish Magnifying Glass', 'hint' => 'It\'s a magnifying glass made from silver. Your pets should be able to make one.' ];
            case 50: return [ 'itemName' => 'Gold Key', 'hint' => 'Your pets will need to make one. Out of gold.' ];
            case 60: return [ 'itemName' => 'Magpie\'s Deal', 'hint' => 'You\'ll have to trade with a magpie. Those birds are pretty smart in a lot of ways, but pretty dumb in others. A lot of people on the island trick them with "Gold" Idols.' ];
            case 70: return [ 'itemName' => 'Fairy Ring', 'hint' => 'You\'ll need to keep a Fireplace burning for a little while. Talk to some other fairy about getting a Fireplace, if you still don\'t have one.' ];
            case 80: return [ 'itemName' => 'Antenna', 'hint' => 'Do you not have a Beehive? Well, I guess I\'m not surprised: most bees probably don\'t want to deal with you. I\'d try asking some ants, instead.' ];
            case 90: return [ 'itemName' => 'Benjamin Franklin', 'hint' => 'Your pets will need to make this. A Silver Key is, well, key to making one. Sorry about the pun. I try to avoid those as much as possible.' ];
            case 100: return [ 'itemName' => 'Piece of Cetgueli\'s Map', 'hint' => 'I\'d start by looking in sunken treasure chests. It\'s a pirate thing. You know how pirates are.' ];
            case 110: return [ 'itemName' => 'Blood Wine', 'hint' => 'Oh, this one is actually a little dangerous. One of your pets will have to steal some from a Vampire. Vampires are a bit easier to find in the Umbra, which is already a dangerous place, so, you know: good luck; have fun.' ];
            case 120: return [ 'itemName' => 'Cheese Omelette with Salsa', 'hint' => 'Really? You take a Cheese Omelette, and you put Salsa on top.' ];
            case 130: return [ 'itemName' => 'WINE', 'hint' => 'It\'s prepared from a Macintosh, which I believe you can find in Project-E.' ];
            case 140: return [ 'itemName' => 'Lightning Sword', 'hint' => 'The components should be pretty obvious. As for where to get some lightning, the higher, the better.' ];
        }

        return null;
    }

    public function logExchange(Inventory $itemForSale): DailyMarketInventoryTransaction
    {
        $log = (new DailyMarketInventoryTransaction())
            ->setInventory($itemForSale->getId())
            ->setItem($itemForSale->getItem())
            ->setPrice($itemForSale->getBuyPrice())
        ;

        $this->em->persist($log);

        return $log;
    }

    public function transferItemToPlayer(Inventory $item, User $newOwner, int $location)
    {
        $this->userStatsRepository->incrementStat($item->getOwner(), UserStatEnum::TOTAL_MONEYS_EARNED_IN_MARKET, $item->getSellPrice());
        $this->userStatsRepository->incrementStat($item->getOwner(), UserStatEnum::ITEMS_SOLD_IN_MARKET, 1);
        $this->userStatsRepository->incrementStat($newOwner, UserStatEnum::ITEMS_BOUGHT_IN_MARKET, 1);

        $item
            ->setOwner($newOwner)
            ->setSellPrice(null)
            ->setLocation($location)
            ->setModifiedOn()
        ;

        if($item->getLunchboxItem())
            $this->em->remove($item->getLunchboxItem());

        if($item->getHolder())
            $item->getHolder()->setTool(null);

        if($item->getWearer())
            $item->getWearer()->setHat(null);
    }
}
