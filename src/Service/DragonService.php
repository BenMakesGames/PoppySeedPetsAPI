<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\CalendarFunctions;
use App\Functions\DragonHelpers;
use App\Functions\EnchantmentRepository;
use App\Functions\PetBadgeHelpers;
use App\Functions\PlayerLogFactory;
use App\Functions\SpiceRepository;
use App\Functions\UserQuestRepository;
use App\Service\PetActivity\TreasureMapService;
use Doctrine\ORM\EntityManagerInterface;

class DragonService
{
    // currently sums to 75
    private const array SilverGoodies = [
        [ 'weight' => 30, 'item' => 'Liquid-hot Magma' ], // 40%
        [ 'weight' => 15, 'item' => 'Quintessence' ], // 20%
        [ 'weight' => 15, 'item' => 'Charcoal' ], // 20%

        // 20% chance of one of these:
        [ 'weight' => 5, 'item' => 'Magpie Pouch' ],
        [ 'weight' => 5, 'item' => 'Fruits & Veggies Box', 'spice' => 'Well-done' ],
        [ 'weight' => 5, 'item' => 'Handicrafts Supply Box' ],
    ];

    // currently sums to 100 - handy!
    private const array GoldGoodies = [
        [ 'weight' => 20, 'item' => 'Liquid-hot Magma' ], // 20%
        [ 'weight' => 20, 'item' => 'Tiny Scroll of Resources' ],
        [ 'weight' => 10, 'item' => 'Dark Matter' ],
        [ 'weight' => 10, 'item' => 'Raccoon Pouch', 'spice' => 'Well-done' ],
        [ 'weight' => 10, 'item' => 'Rock' ],
        [ 'weight' => 10, 'item' => 'Burnt Log' ],

        // 20% chance of a burnt, iron tool:
        [ 'weight' => 10, 'item' => 'Iron Sword', 'bonus' => 'Burnt' ],
        [ 'weight' => 5, 'item' => 'Dumbbell', 'bonus' => 'Burnt' ],
        [ 'weight' => 5, 'item' => 'Flute', 'bonus' => 'Burnt' ],
    ];

    // currently sums to 75
    private const array GemGoodies = [
        [ 'weight' => 25, 'item' => 'Scroll of Resources' ], // 33%
        [ 'weight' => 15, 'item' => 'Liquid-hot Magma' ], // 20%
        [ 'weight' => 10, 'item' => 'Firestone' ], // 13.3333%
        // ^ 2/3 chance of being one of those

        // 1/3 chance of being one of these:
        [ 'weight' => 5, 'item' => 'Box of Ores' ], // 6.6666%
        [ 'weight' => 5, 'item' => 'Secret Seashell' ], // 6.6666%
        [ 'weight' => 5, 'item' => 'Scroll of Resources' ], // 6.6666%
        [ 'weight' => 5, 'item' => 'Stereotypical Bone' ], // 6.6666%
        [ 'weight' => 3, 'item' => 'Rib' ], // 4%
        [ 'weight' => 2, 'item' => 'Dino Skull' ], // 2.6666%
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly ResponseService $responseService,
        private readonly Clock $clock,
        private readonly UserStatsService $userStatsRepository,
        private readonly IRandom $rng,
        private readonly HattierService $hattierService,
        private readonly TransactionService $transactionService
    )
    {
    }

    /**
     * @param int[] $itemIds
     */
    public function giveTreasures(User $user, array $itemIds): string
    {
        $dragon = DragonHelpers::getAdultDragon($this->em, $user);

        if(!$dragon)
            throw new PSPNotFoundException('You don\'t have an adult dragon!');

        if($dragon->getHostage())
            throw new PSPInvalidOperationException('"This \'hostage\' is giving me a headache - I can\'t even count my gold! Can you please do something?"');

        $user = $dragon->getOwner();

        $items = $this->em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.treasure', 'treasure')
            ->andWhere('i.id IN (:inventoryIds)')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.treasure IS NOT NULL')
            ->setParameter('inventoryIds', $itemIds)
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::Home)
            ->getQuery()
            ->execute()
        ;

        if(count($items) < count($itemIds))
            throw new PSPNotFoundException('Some of the treasures selected... maybe don\'t exist!? That shouldn\'t happen. Reload and try again.');

        $silver = ArrayFunctions::sum($items, fn(Inventory $i) => $i->getItem()->getTreasure()->getSilver());
        $gold = ArrayFunctions::sum($items, fn(Inventory $i) => $i->getItem()->getTreasure()->getGold());
        $gems = ArrayFunctions::sum($items, fn(Inventory $i) => $i->getItem()->getTreasure()->getGems());

        $offeringItemNames = [];

        foreach($items as $item)
        {
            $offeringItemNames[] = $item->getItem()->getNameWithArticle();
            $this->em->remove($item);
        }

        sort($offeringItemNames);

        $this->userStatsRepository->incrementStat($user, UserStat::TreasuresGivenToDragonHoard, count($items));

        $silverGoodies = self::SilverGoodies;
        $goldGoodies = self::GoldGoodies;
        $gemGoodies = self::GemGoodies;

        if(CalendarFunctions::isValentinesOrAdjacent($this->clock->now))
        {
            $silverGoodies[] = [ 'weight' => 10, 'item' => 'Cacao Fruit' ];
            $goldGoodies[] = [ 'weight' => 10, 'item' => 'Chocolate Bar' ];
            $gemGoodies[] = [ 'weight' => 10, 'item' => 'Chocolate Key' ];
        }

        $chineseCalendarInfo = CalendarFunctions::getChineseCalendarInfo($this->clock->now);

        if($chineseCalendarInfo->month === 1 && $chineseCalendarInfo->day <= 6)
        {
            $silverGoodies[] = [ 'weight' => 10, 'item' => 'Mooncake' ];
            $goldGoodies[] = [ 'weight' => 10, 'item' => 'Mooncake' ];
            $gemGoodies[] = [ 'weight' => 10, 'item' => 'Mooncake' ];
        }

        $goodies = [];

        $previousSilver = $dragon->getSilver();
        $previousGold = $dragon->getGold();

        if($silver > 0)
        {
            $dragon->increaseSilver($silver);

            for($i = 0; $i < $silver; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted($silverGoodies, fn($i) => $i['weight']);
        }

        if($gold > 0)
        {
            $dragon->increaseGold($gold);

            for($i = 0; $i < $gold; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted($goldGoodies, fn($i) => $i['weight']);
        }

        if($dragon->getSilver() >= 200 && $dragon->getGold() >= 200 && ($previousGold < 200 || $previousSilver < 200))
        {
            $this->unlockLoadedHattierStyle($user);
        }

        if($gems > 0)
        {
            $previousGems = $dragon->getGems();

            $dragon->increaseGems($gems);

            for($i = 0; $i < $gems; $i++)
                $goodies[] = ArrayFunctions::pick_one_weighted($gemGoodies, fn($i) => $i['weight']);

            if($previousGems < 50 && $dragon->getGems() >= 50)
                $this->unlockWhiteDiamondHattierStyle($user);

            if($previousGems < 100 && $dragon->getGems() >= 100)
                $this->unlockBlackDiamondHattierStyle($user);
        }

        if(CalendarFunctions::isApricotFestival($this->clock->now))
        {
            $gotReward = UserQuestRepository::findOrCreate($this->em, $user, 'Got Apricot Festival ' . $this->clock->now->format('Y') . ' Dragon Reward', 0);

            if($gotReward->getValue() === 0)
            {
                $goodies[] = [ 'weight' => 0, 'item' => 'Gold and Apricots', 'locked' => true ];
                $gotReward->setValue(1);
            }
        }

        foreach($goodies as $goody)
        {
            $newItem = $this->inventoryService->receiveItem($goody['item'], $user, $user, $user->getName() . ' received this from their dragon, ' . $dragon->getName() . '.', LocationEnum::Home);

            if(array_key_exists('bonus', $goody)) $newItem->setEnchantment(EnchantmentRepository::findOneByName($this->em, $goody['bonus']));
            if(array_key_exists('spice', $goody)) $newItem->setSpice(SpiceRepository::findOneByName($this->em, $goody['spice']));
            if(array_key_exists('locked', $goody)) $newItem->setLockedToOwner(true);
        }

        $totalMoneys = 0;
        $extraItem = null;

        $helperAdjectives = [];

        if($dragon->getHelper())
        {
            $helper = $dragon->getHelper();
            $helperSkills = $helper->getComputedSkills();

            $businessSkill = $helperSkills->getIntelligence()->getTotal() +
                ($helper->hasMerit(MeritEnum::EIDETIC_MEMORY) ? 3 : 0) +
                ($helper->hasMerit(MeritEnum::GREGARIOUS) ? 2 : 0) +
                ($helper->hasMerit(MeritEnum::LUCKY) ? 1 : 0)
            ;

            if($helper->hasMerit(MeritEnum::EIDETIC_MEMORY)) $helperAdjectives[] = 'flawlessly-organized';
            if($helper->hasMerit(MeritEnum::GREGARIOUS)) $helperAdjectives[] = 'incredibly-sociable';
            if($helper->hasMerit(MeritEnum::LUCKY)) $helperAdjectives[] = 'surprisingly-lucky';

            $moneysMultiplier = $gems * 0.5 + $gold * 0.35 + $silver * 0.25;
            $workMultiplier = $gems * 3 + $gold * 2 + $silver;

            $dragon
                ->addEarnings($businessSkill * $moneysMultiplier)
                ->addByproductProgress(($businessSkill + 4) * $workMultiplier)
            ;

            if($dragon->getEarnings() >= 1)
            {
                $totalMoneys = (int)$dragon->getEarnings();
                $dragon->addEarnings(-$totalMoneys);

                $this->transactionService->getMoney($user, $totalMoneys, 'Earned by ' . $helper->getName() . ' by investing some of your dragon\'s wealth.', [ 'Dragon Den' ]);

                PetBadgeHelpers::awardBadgeAndLog($this->em, $helper, PetBadgeEnum::WasAnAccountant, ActivityHelpers::PetName($helper) . ' made money for your dragon through skilled investing.');
            }

            if($dragon->getByproductProgress() >= 100)
            {
                $dragon->addByproductProgress(-100);

                $possibleItems = TreasureMapService::getFluffmongerFlavorFoods($helper->getFavoriteFlavor());

                if($helperSkills->getNature()->getTotal() >= 5)
                    $possibleItems[] = 'Large Bag of Fertilizer';

                if($helperSkills->getScience()->getTotal() >= 5)
                    $possibleItems[] = 'Space Junk';

                if($helperSkills->getArcana()->getTotal() >= 5 || $helper->hasMerit(MeritEnum::NATURAL_CHANNEL))
                    $possibleItems[] = 'Quintessence';

                if($helper->hasMerit(MeritEnum::LOLLIGOVORE))
                    $possibleItems[] = 'Tentacle Fried Rice';

                if($helperSkills->getSexDrive()->getTotal() >= 1)
                    $possibleItems[] = 'Goodberries';

                if($helperSkills->getMusic()->getTotal() >= 5)
                    $possibleItems[] = 'Musical Scales';

                if($helperSkills->getCrafts()->getTotal() >= 5)
                    $possibleItems[] = 'Handicrafts Supply Box';

                $extraItemName = $this->rng->rngNextFromArray($possibleItems);

                $extraItem = $this->inventoryService->receiveItem($extraItemName, $user, $user, $user->getName() . ' received this from their dragon, ' . $dragon->getName() . ' (and ' . ArrayFunctions::list_nice($helperAdjectives) . ' helper, ' . $dragon->getHelper()->getName() . ').', LocationEnum::Home);
            }
        }

        $this->em->flush();

        $itemNames = array_map(fn($goodie) => $goodie['item'], $goodies);
        sort($itemNames);

        $message = $dragon->getName() . ' thanks you for your gift, and gives you ' . ArrayFunctions::list_nice_sorted($itemNames) . ' in exchange';

        if($totalMoneys > 0)
        {
            if($extraItem)
                $message .= ', plus ' . $totalMoneys . '~~m~~ and ' . $extraItem->getItem()->getNameWithArticle() . ' earned in investments (thanks to their ' . ArrayFunctions::list_nice($helperAdjectives) . ' helper, ' . $dragon->getHelper()->getName() . '!)';
            else
                $message .= ', plus ' . $totalMoneys . '~~m~~ earned in investments (thanks to ' . $dragon->getHelper()->getName() . '\'s help!)';
        }
        else if($extraItem)
        {
            $message .= ', plus ' . $extraItem->getItem()->getNameWithArticle() . ', which they earned from a particularly-lucrative deal (thanks to their ' . ArrayFunctions::list_nice($helperAdjectives) . ' helper, ' . $dragon->getHelper()->getName() . '!)';
        }
        else
            $message .= '.';

        PlayerLogFactory::create(
            $this->em,
            $user,
            'You gave your dragon ' . ArrayFunctions::list_nice($offeringItemNames) . '. ' . $message,
            [ 'Dragon Den' ]
        );

        return $message;
    }

    private function unlockWhiteDiamondHattierStyle(User $user): void
    {
        $enchantment = EnchantmentRepository::findOneByName($this->em, 'with White Diamonds');

        $this->transactionService->getRecyclingPoints($user, 100, 'You received this from your dragon, for donating 50 gems.', [ 'Dragon Den' ]);

        $this->hattierService->playerUnlockAura($user, $enchantment, 'You received this from your dragon, for donating 50 gems.');

        $this->responseService->addFlashMessage('50 whole gems! Your dragon bestows an aura of White Diamonds upon you and your pets! (You can find it at the Hattier\'s!) Oh: and 100 recycling points! Dang! Nice!');
    }

    private function unlockBlackDiamondHattierStyle(User $user): void
    {
        $enchantment = EnchantmentRepository::findOneByName($this->em, 'with Black Diamonds');

        $this->transactionService->getRecyclingPoints($user, 100, 'You received this from your dragon, for donating 100 gems.', [ 'Dragon Den' ]);

        $this->hattierService->playerUnlockAura($user, $enchantment, 'You received this from your dragon, for donating 100 gems.');

        $this->responseService->addFlashMessage('100 entire gems! Your dragon bestows an aura of Black Diamonds upon you and your pets! (You can find it at the Hattier\'s!) Oh: and 100 recycling points! Wow! Super!');
    }

    private function unlockLoadedHattierStyle(User $user): void
    {
        $enchantment = EnchantmentRepository::findOneByName($this->em, 'Loaded');

        $this->transactionService->getRecyclingPoints($user, 100, 'You received this from your dragon, for donating 200 gold and 200 silver.', [ 'Dragon Den' ]);

        $this->hattierService->playerUnlockAura($user, $enchantment, 'You received this from your dragon, for donating 200 gold and 200 silver.');

        $this->responseService->addFlashMessage('200 gold and 200 silver! Your dragon bestows an aura of Coins upon you and your pets! (You can find it at the Hattier\'s!) Oh: and 100 recycling points! Goodness! Fantastic!');
    }
}