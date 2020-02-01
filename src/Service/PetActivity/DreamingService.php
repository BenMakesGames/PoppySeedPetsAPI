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
            'Renaming Scroll', 'Behatting Scroll', 'Unicorn Horn', 'Compass (the Math Kind)', 'Password',
            'Beans', 'Chocomilk', 'Dark Matter', 'Pointer', 'Magic Smoke', 'Useless Fizz', 'Cobbler Recipe',
            'Honeydont', 'Crooked Stick', 'Seaweed', 'Tentacle', 'Secret Seashell', 'Plastic Idol', 'Fig',
            'Silica Grounds', 'Smallish Pumpkin', 'Paper Bag', 'Feathers', 'Gold Triangle', 'Single',
            'Sweet Beet', 'Mysterious Seed', 'Tomato "Sushi"', 'Tentacle Onigiri', 'Candle', 'Wheat Flower',
            'Sweet Coffee Bean Tea with Mammal Extract', 'Trout Yogurt', 'Bungee Cord', 'Paper', 'Sunflower',
            'Striped Microcline', 'Stroganoff Recipe', 'Spirit Polymorph Potion Recipe', 'Giant Turkey Leg',
            'Handicrafts Supply Box',
        ]);

        $dream = ArrayFunctions::pick_one([
            [
                '%dreamer% was %wandering% %location1%, when they spotted a %pet_adjective% %species%. It whispered something, but %dreamer% can\'t remember what, and gave %dreamer% %item%.',
                'A %pet_adjective% %species% gave this to %dreamer% in a dream.',
            ],
            [
                'While %wandering% %location1%, %dreamer% spotted %item%. They grabbed it, and when they looked up, they were %location2%.',
                '%dreamer% found this while %wandering% %location% in a dream.',
            ],
            [
                '%dreamer% tripped over %item%, and tumbled into a pit %location1%. The %item% fell in, too, and %dreamer% grabbed it, and ate it.',
                '%dreamer% ate this while falling in a dream.',
            ],
            [
                '%dreamer% and a friend were %wandering% %location%. The friend reached into their pocket and pulled out something covered in cloth. %dreamer% lifted up the cloth, and found %item%. When they looked up, their friend was gone.',
                '%dreamer% received this from a friend in a dream.'
            ],
            [
                '%dreamer% and a %species% were making out on %surface%. A %item% got in the way, so %dreamer% tossed it aside.',
                '%dreamer%, um, found this in a dream.'
            ],
            [
                '%dreamer% got in a fight with a %species% %location1%. The %species% threw %item% at %dreamer%, and declared victory! >:(',
                'A stupid %species% threw this at %dreamer% in a dream.',
            ],
            [
                '%dreamer% and a friend went out to eat, and ordered %item%. When it arrived, it was %more% than expected!',
                '%dreamer% ordered this at a restaurant in a dream.',
            ],
            [
                '%dreamer% saw their parents, but couldn\'t make them out. They hummed a familiar tune, and handed %dreamer% %item%.',
                '%dreamer% got this from their parents in a dream.',
            ],
            [
                '%dreamer% found a secret compartment %location1%. They crawled inside, and arrived %location2%. On %surface%, there was %item%. %dreamer% took it.',
                '%dreamer% found this on %surface% %location2% in a dream.',
            ]
        ]);

        $locationIndicies = array_rand(self::LOCATIONS, 2);

        $replacements = [
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
