<?php
declare(strict_types=1);

namespace App\Service\PetActivity;

use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetSpecies;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Functions\GrammarFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Repository\DreamRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class DreamingService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly ResponseService $responseService,
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly DreamRepository $dreamRepository,
        private readonly EntityManagerInterface $em
    )
    {
    }

    private const Locations = [
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

    private const WanderingWords = [
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

    public const RandomPluralStuff = [
        'bolts of silk', 'piles of treasure', 'boxes of %item%', 'people',
        'piles of snow', 'cobwebs', 'those ball pit balls', 'ringing phones',
        'piles of cotton candy (either the candy, or the pet; it wasn\'t clear)',
        'tiny snails', 'piles of ice cream', 'fluttering pieces of paper'
    ];

    public function dream(Pet $pet): PetActivityLog
    {
        $possibleItems = [
            'Beans',
            'Canned Food', 'Carrot Wine Recipe', 'Chanterelle', 'Chocomilk', 'Cobbler Recipe', 'Cucumber',
            'Egg',
            'Fig',
            'Giant Turkey Leg', 'Ginger',
            'Honeydont',
            'Oil', 'Orange',
            'Pineapple',
            'Purple Violet',
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

        $dream = $this->dreamRepository->findRandom($this->rng);

        /** @var PetSpecies $species */
        $species = $this->rng->rngNextFromArray($this->em->getRepository(PetSpecies::class)->findAll());

        $replacements = $this->generateReplacementsDictionary($item, $pet, $species);

        $eventDescription = DreamingService::applyMadlib($dream->getDescription(), $replacements);
        $itemDescription = DreamingService::applyMadlib($dream->getItemDescription(), $replacements);

        $this->inventoryService->receiveItem($itemName, $pet->getOwner(), $pet->getOwner(), $itemDescription, LocationEnum::HOME);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $log = $this->responseService->createActivityLog($pet, $eventDescription, '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Dream' ]))
        ;

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $log);

        return $log;
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
}
