<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\PetSpeciesRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class DreamingService
{
    private $inventoryService;
    private $responseService;
    private $petSpeciesRepository;
    private $petExperienceService;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetSpeciesRepository $petSpeciesRepository,
        PetExperienceService $petExperienceService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petSpeciesRepository = $petSpeciesRepository;
        $this->petExperienceService = $petExperienceService;
    }

    private const LOCATIONS = [
        'in a mall',
        'on a mountain path',
        'in some ruins',
        'on a cliff-edge',
        'at home',
        'on Mercury',
        'in an underwater castle',
        'in a swamp',
        'on a pirate ship',
        'in a forest',
        'in a huge treehouse',
    ];

    private const WANDERING_WORDS = [
        'wandering',
        'running',
        'searching for something',
        'taking a break',
        'chilling',
    ];

    public function dream(Pet $pet): PetActivityLog
    {
        $item = ArrayFunctions::pick_one([
            'Beans', 'Bungee Cord',
            'Candle', 'Chanterelle', 'Chocomilk', 'Cobbler Recipe', 'Compass (the Math Kind)', 'Crooked Stick',
            'Egg',
            'Feathers', 'Fig', 'Fluff',
            'Giant Turkey Leg', 'Ginger', 'Gold Triangle',
            'Handicrafts Supply Box', 'Honeydont',
            'Iron Key',
            'Music Note', 'Mysterious Seed',
            'Oil', 'Orange',
            'Paper', 'Paper Bag', 'Password', 'Plastic', 'Plastic Idol', 'Purple Violet',
            'Quintessence',
            'Red',
            'Seaweed', 'Secret Seashell', 'Silica Grounds', 'Single', 'Smallish Pumpkin',
            'Spirit Polymorph Potion Recipe', 'String', 'Striped Microcline', 'Stroganoff Recipe', 'Sunflower',
            'Sweet Beet', 'Sweet Coffee Bean Tea with Mammal Extract',
            'Tentacle', 'Tentacle Onigiri', 'Tomato "Sushi"', 'Trout Yogurt',
            'Unicorn Horn', 'Useless Fizz',
            'Wheat Flower', 'Witch-hazel',
        ]);

        $dream = ArrayFunctions::pick_one([
            [
                'In a dream, %dreamer% was %wandering% %location1%, when they spotted a %pet_adjective% %species%. It whispered something, but %dreamer% can\'t remember what, and gave %dreamer% %item%.',
                'A %pet_adjective% %species% gave this to %dreamer% in a dream.',
            ],
            [
                'While %wandering% %location1% in a dream, %dreamer% spotted %item%. They reached down and grabbed it, and when they looked up, they were %location2%.',
                '%dreamer% found this in a %location2% in a dream.',
            ],
            [
                '%dreamer% dreamed they tripped over %item%, and tumbled into a pit %location1%. The %item% fell in, too, and %dreamer% grabbed it, and ate it.',
                '%dreamer% ate this while falling in a dream.',
            ],
            [
                'In a dream, %dreamer% and a friend were %wandering% %location%. The friend reached into their pocket and pulled out something covered in cloth. %dreamer% lifted up the cloth, and found %item%. When they looked up, their friend was gone.',
                '%dreamer% received this from a friend in a dream.'
            ],
            [
                '%dreamer% dreamed that they were making out with a %species% on %surface%. A %item% got in the way, so %dreamer% tossed it aside.',
                '%dreamer%, um, found this in a dream.'
            ],
            [
                'In a dream, %dreamer% got in a fight with a %species% %location1%. The %species% threw %item% at %dreamer%, and declared victory! >:(',
                'A stupid %species% threw this at %dreamer% in a dream.',
            ],
            [
                'In a dream, %dreamer% and a friend went out to eat, and ordered %item%. When it arrived, it was %more% than expected!',
                '%dreamer% ordered this at a restaurant in a dream.',
            ],
            [
                '%dreamer% saw their parents in a dream, but couldn\'t make them out. They hummed a familiar tune, and handed %dreamer% %item%.',
                '%dreamer% got this from their parents in a dream.',
            ],
            [
                'In a dream, %dreamer% found a secret compartment %location1%. They crawled inside, and arrived %location2%. On %surface%, there was %item%. %dreamer% took it.',
                '%dreamer% found this on %surface% %location2% in a dream.',
            ]
        ]);

        $locationIndicies = array_rand(self::LOCATIONS, 2);

        $replacements = [
            '%item%' => $item,
            '%dreamer%' => $pet->getName(),
            '%location1%' => self::LOCATIONS[$locationIndicies[0]],
            '%location2%' => self::LOCATIONS[$locationIndicies[1]],
            '%wandering%' => ArrayFunctions::pick_one(self::WANDERING_WORDS),
            '%species%' => ArrayFunctions::pick_one($this->petSpeciesRepository->findAll())->getName(),
            '%pet_adjective%' => ArrayFunctions::pick_one([ 'colorful', 'suspicious-looking', 'strong', 'big', 'small', 'cute', 'dangerous-looking', 'hungry', 'lost' ]),
            '%more%' => ArrayFunctions::pick_one([ 'bigger', 'more colorful', 'smaller', 'more fragrant', 'undulating more', 'paler', 'shinier' ]),
            '%surface%' => ArrayFunctions::pick_one([ 'a table', 'a moss-covered rock', 'the floor', 'a pile of pillows', 'a sturdy box' ])
        ];

        $eventDescription = str_replace(array_keys($replacements), $replacements, $dream[0]);
        $itemDescription = str_replace(array_keys($replacements), $replacements, $dream[1]);

        $this->inventoryService->receiveItem($item, $pet->getOwner(), $pet->getOwner(), $itemDescription, LocationEnum::HOME);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

        return $this->responseService->createActivityLog($pet, $eventDescription, '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
        ;
    }
}
