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

namespace App\Service\PetActivity;

use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetSpecies;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Functions\GrammarFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class DreamingService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em
    )
    {
    }

    private const array Locations = [
        'in a mall',
        'on a mountain path',
        'in some ruins',
        'on a cliff-edge',
        'at home',
        'on %planet%',
        'in an underwater castle',
        'in a swamp',
        'on a pirate ship',
        'in a forest',
        'in a huge treehouse',
        'near a train station',
        'in an airport',
        'on a boat',
        'on a spaceship',
        'on the beach',
        'in some crystal caves',
        'in a warehouse',
        'at a water park',
        'on a snow-capped mountain',
        'in a laboratory',
        'on a space station',
        'in a crowded bar',
        'in a junkyard',
        'in a dilapidated arena',
    ];

    private const array WanderingWords = [
        'wandering',
        'running',
        'searching for something',
        'taking a break',
        'chilling',
        'treasure-hunting',
        'fighting %a_wandering_monster%',
        'solving a math problem',
        'drinking %a_drink%',
        'eating %a_food%'
    ];

    public const array RandomPluralStuff = [
        'bolts of silk', 'piles of treasure', 'boxes of %item%', 'people',
        'piles of snow', 'cobwebs', 'those ball pit balls', 'ringing phones',
        'piles of cotton candy (either the candy, or the pet; it wasn\'t clear)',
        'tiny snails', 'piles of ice cream', 'fluttering pieces of paper'
    ];

    public function dream(Pet $pet): PetActivityLog
    {
        $possibleItems = [
            'Beans',
            'Canned Food', 'Carrot Wine Recipe', 'Cellular Peptide Cake', 'Chanterelle', 'Chocomilk', 'Cobbler Recipe', 'Cucumber',
            'Egg',
            'Fig',
            'Giant Turkey Leg', 'Ginger',
            'Honeydont',
            'Jelly-filled Donut',
            'Oil', 'Olives', 'Orange',
            'Pineapple', 'Plastic Bottle', 'Purple Violet',
            'Red',
            'Seaweed', 'Smallish Pumpkin',
            'Stroganoff Recipe', 'Sunflower',
            'Sweet Beet', 'Sweet Coffee Bean Tea with Mammal Extract',
            'Tentacle', 'Tentacle Onigiri', 'Tomato "Sushi"', 'Trout Yogurt',
            'Wheat Flower', 'Witch-hazel',
            'Yellowy Lime'
        ];

        if($pet->getFood() + $pet->getJunk() > 0)
        {
            $possibleItems = array_merge($possibleItems, [
                'Bungee Cord',
                'Candle', 'Compass (the Math Kind)', 'Crooked Stick',
                'Feathers', 'Fez', 'Fluff',
                'Gold Triangle',
                'Handicrafts Supply Box',
                $this->rng->rngNextFromArray([ 'Iron Key', 'Iron Key', 'Silver Key', 'Gold Key' ]),
                'Jar of Fireflies',
                'Music Note', 'Mysterious Seed',
                'Paper', 'Paper Bag', 'Password', 'Plastic', 'Plastic Idol',
                'Quintessence',
                'Secret Seashell', 'Silica Grounds', 'Single',
                'Spirit Polymorph Potion Recipe', 'String', 'Striped Microcline',
                'Unicorn Horn', 'Useless Fizz',
            ]);
        }

        $itemName = $this->rng->rngNextFromArray($possibleItems);
        $item = ItemRepository::findOneByName($this->em, $itemName);

        $dream = $this->findRandomDream($this->rng);

        /** @var PetSpecies $species */
        $species = $this->rng->rngNextFromArray($this->em->getRepository(PetSpecies::class)->findAll());

        $replacements = $this->generateReplacementsDictionary($item, $pet, $species);

        $eventDescription = DreamingService::applyMadlib($dream['description'], $replacements);
        $itemDescription = DreamingService::applyMadlib($dream['itemDescription'], $replacements);

        $this->inventoryService->receiveItem($itemName, $pet->getOwner(), $pet->getOwner(), $itemDescription, LocationEnum::Home);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, $eventDescription)
            ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Dream ]))
        ;

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PulledAnItemFromADream, $log);

        return $log;
    }

    public function findRandomDream(IRandom $rng): array
    {
        return $rng->rngNextFromArray(self::Dreams);
    }

    public function generateReplacementsDictionary(Item $item, Pet $pet, PetSpecies $species): array
    {
        $locations = $this->rng->rngNextSubsetFromArray(self::Locations, 2);

        $monsters = [
            [ 'a' => 'a goblin', 'the' => 'the goblin' ],
            [ 'a' => 'a dragon', 'the' => 'the dragon' ],
            [ 'a' => 'a huge ant', 'the' => 'the ant' ],
            [ 'a' => 'a plant-monster', 'the' => 'the plant-monster' ],
            [ 'a' => 'their own shadow', 'the' => 'their own shadow' ]
        ];

        $petOrMonsterIsPet = $this->rng->rngNextBool();

        $this->rng->rngNextShuffle($monsters);

        return [
            '%item%' => $item->getName(),
            '%item_with_article%' => $item->getNameWithArticle(),
            '%Item_with_article%' => ucfirst($item->getNameWithArticle()),
            '%dreamer%' => $pet->getName(),
            '%location1%' => $locations[0],
            '%location2%' => $locations[1],
            '%Location1%' => ucfirst($locations[0]),
            '%Location2%' => ucfirst($locations[1]),
            '%wandering%' => $this->rng->rngNextFromArray(self::WanderingWords),
            '%species%' => $species->getName(),
            '%a_species%' => GrammarFunctions::indefiniteArticle($species->getName()) . ' ' . $species->getName(),
            '%adverb%' => $this->rng->rngNextFromArray([ 'hesitantly', 'eagerly', 'grumpily', 'apathetically' ]),
            '%pet_adjective%' => $this->rng->rngNextFromArray([ 'colorful', 'suspicious-looking', 'strong', 'big', 'small', 'cute', 'dangerous-looking', 'hungry', 'lost', 'cheerful' ]),
            '%more%' => $this->rng->rngNextFromArray([ 'bigger', 'more colorful', 'smaller', 'more fragrant', 'undulating more', 'paler', 'shinier', 'stickier', 'more fabulous' ]),
            '%surface%' => $this->rng->rngNextFromArray([
                'a table', 'a moss-covered rock', 'the floor', 'a pile of pillows', 'a sturdy box',
                'a raw slab of acorn fugu', 'the roof of a skyscraper', 'a wobbly chair', 'a huge beanbag',
                'a pool table', 'a picnic bench'
            ]),
            '%planet%' => $this->rng->rngNextFromArray([ 'the Moon', 'Mars', 'Pluto', 'Enceladus', 'Vesta', 'Venus', 'Phobetor' ]),
            '%a_drink%' => $this->rng->rngNextFromArray([ 'a chai milkshake', 'a mango lassi', 'some tea', 'some fruit punch', 'some coconut cordial', 'some blue milk' ]),
            '%a_food%' => $this->rng->rngNextFromArray([ 'a cellular peptide cake', 'a piece of naan', 'a slice of za', 'some donburi', 'a lobster', 'some succotash', 'a bowl of chili', 'a plate of tiny snails' ]),
            '%a_food_or_drink%' => $this->rng->rngNextFromArray([ '%a_food%', '%a_drink%' ]),
            '%a_monster%' => $monsters[0]['a'],
            '%A_monster%' => ucfirst($monsters[0]['a']),
            '%the_monster%' => $monsters[0]['the'],
            '%a_wandering_monster%' => $monsters[1]['a'],
            '%a_pet_or_monster%' => $petOrMonsterIsPet ? 'a %pet_adjective% %species%' : '%a_monster%',
            '%A_pet_or_monster%' => $petOrMonsterIsPet ? 'A %pet_adjective% %species%' : '%A_monster%',
            '%plural_stuff%' => $this->rng->rngNextFromArray(self::RandomPluralStuff),
        ];
    }

    public static function applyMadlib(string $text, array $replacements): string
    {
        do
        {
            $text = str_replace(array_keys($replacements), $replacements, $text, $replaced);
        } while($replaced > 0);

        return $text;
    }

    public const Dreams = [
        [
            'description' => 'In a dream, %dreamer% was %wandering% %location1%, when they spotted %a_pet_or_monster%. It whispered something, but %dreamer% can\'t remember what, and gave %dreamer% %item%.',
            'itemDescription' => '%A_pet_or_monster% gave this to %dreamer% in a dream.'
        ],
        [
            'description' => 'While %wandering% %location1% in a dream, %dreamer% spotted %item%. They reached down and grabbed it, and when they looked up, they were %location2%.',
            'itemDescription' => '%dreamer% found this in %location1% in a dream.'
        ],
        [
            'description' => '%dreamer% dreamed they tripped over %item%, and tumbled into a pit %location1%. The %item% fell in, too, and %dreamer% grabbed it, and ate it.',
            'itemDescription' => '%dreamer% ate this while falling in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% and a friend were %wandering% %location1%. The friend reached into their pocket and pulled out something covered in cloth. %dreamer% lifted up the cloth, and found %item%. When they looked up, their friend was gone.',
            'itemDescription' => '%dreamer% received this from a friend in a dream.'
        ],
        [
            'description' => '%dreamer% dreamed that they were making out with %a_species% on %surface% %location1%. %Item_with_article% got in the way, so %dreamer% tossed it aside.',
            'itemDescription' => '%dreamer%, um, found this in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% got in a fight with %a_species% %location1%. The %species% threw %item% at %dreamer%, and declared victory! >:(',
            'itemDescription' => 'A stupid %species% threw this at %dreamer% in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% and a friend went out to eat, and ordered %item_with_article% and %a_food_or_drink%. When the %item% arrived, it was %more% than expected!',
            'itemDescription' => '%dreamer% ordered this at a restaurant in a dream.'
        ],
        [
            'description' => '%dreamer% saw their parents in a dream, but couldn\'t make them out. They hummed a familiar tune, and handed %dreamer% %item%.',
            'itemDescription' => '%dreamer% got this from their parents in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% found a secret compartment %location1%. They crawled inside, and arrived %location2%. On %surface%, there was %item%. %dreamer% took it.',
            'itemDescription' => '%dreamer% found this on %surface% %location2% in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% bumped into %a_pet_or_monster%, causing them to drop %item_with_article%. %dreamer% %adverb% picked it up, and tried to call out, but their voice wasn\'t working.',
            'itemDescription' => '%dreamer% %adverb% picked this up in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% was approached by a huge %item%. They %adverb% ran away; as they did so, the %item% shrank. Eventually, %dreamer% stopped, and picked it up.',
            'itemDescription' => '%dreamer% was chased by this in a dream. (It was bigger in the dream...)'
        ],
        [
            'description' => '%Location1% in a dream, %dreamer% looked in a mirror. They were %more% than usual. Also, there was %item_with_article% on their head!',
            'itemDescription' => '%dreamer% saw this on their head while looking in a mirror in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% was %wandering% %location1%, and found themselves %location2%, surrounded by %plural_stuff%! %dreamer% spotted %item% in the distance, reached out for it, then woke up.',
            'itemDescription' => '%dreamer% found this amidst %plural_stuff% in a dream.'
        ],
        [
            'description' => 'In a dream, %dreamer% was in a room full of %plural_stuff%, kissing %a_species%, but the %species% was actually %item_with_article%? Or something? And then they woke up...',
            'itemDescription' => '%dreamer% was kissing this in a dream?? (Don\'t ask; dreams are weird.)'
        ],
        [
            'description' => '%dreamer% thought they were exploring %location1% in a dream, but they were actually %a_monster%, but super-tiny, exploring %surface%. They found %item_with_article% while wandering around (it was also super-tiny), and woke up.',
            'itemDescription' => '%dreamer% found this on %surface% in a dream.'
        ],
        [
            'description' => '%dreamer% got lost in a maze full of %plural_stuff% in a dream. They wandered around for what felt like forever before finally finding the %item% they had been sent to find there. Then they woke up.',
            'itemDescription' => 'In a dream, %dreamer% was sent on a quest to find this... and succeeded!'
        ],
        [
            'description' => '%dreamer% had a dream where they were %a_monster%, but also they could _see_ %the_monster%? And %the_monster% was holding %item_with_article%, which %dreamer% took, even though they already had it... it was weird.',
            'itemDescription' => '%dreamer% took this from their self, in a dream, when they were %a_monster%.'
        ]
    ];
}
