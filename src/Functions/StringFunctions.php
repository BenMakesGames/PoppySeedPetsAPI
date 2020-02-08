<?php
namespace App\Functions;

final class StringFunctions
{
    public static function isISO88591(string $string): bool
    {
        $ISOd = iconv('UTF-8', 'ISO-8859-1//IGNORE', $string);
        return mb_strlen($string) === strlen($ISOd);
    }

    public static function startsWith(string $haystack, string $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    public static function randomPassword(): string
    {
        $words = [];

        for($i = 0; $i < 4; $i++)
            $words[] = ArrayFunctions::pick_one(self::WORDS);

        return implode(' ', $words);
    }

    public static function randomLettersAndNumbers(int $length): string
    {
        return self::randomString('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', $length);
    }

    public static function randomString(string $characters, int $length): string
    {
        $string = '';

        for($i = 0; $i < $length; $i++)
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];

        return $string;
    }

    private const WORDS = [
        'hovercraft', 'oath', 'packer', 'candy', 'titan', 'wire', 'road', 'circuit', 'mixer', 'culture',
        'center', 'receptor', 'ireland', 'manor', 'performance', 'history', 'facility', 'room', 'revolver', 'wheat',
        'system', 'environment', 'tournament', 'field', 'processor', 'africa', 'member', 'maiden', 'career', 'footballer',
        'district', 'deer', 'temple', 'olympics', 'rook', 'poland', 'collection', 'tabby', 'studio', 'book',
        'peacock', 'affiliate', 'research', 'operator', 'software', 'team', 'lifestyle', 'continent', 'quest', 'sorcery',
        'guard', 'character', 'parent', 'actual', 'comic', 'graph', 'vertex', 'algebra', 'product', 'policy',
        'thailand', 'payment', 'war', 'weakness', 'claims', 'honeydew', 'species', 'jacket', 'father', 'garden',
        'campground', 'beach', 'picnic', 'story', 'narrative', 'scout', 'wildflower', 'hemlock', 'butterfly', 'decline',
        'britain', 'society', 'largest', 'kingdom', 'beaver', 'creek', 'colorado', 'david', 'scott', 'opera',
        'bank', 'year', 'buffalo', 'bill', 'seven', 'waldo', 'amanda', 'brown', 'flute', 'sonata',

        'april', 'library', 'chamber', 'source', 'festival', 'feature', 'california', 'horse', 'mile', 'general',
        'movie', 'gong', 'american', 'tracy', 'play', 'brother', 'budget', 'public', 'germany', 'achievement',
        'spider', 'family', 'attack', 'sixteen', 'police', 'officer', 'machete', 'movement', 'danger', 'china',
        'street', 'household', 'monday', 'saturday', 'market', 'blood', 'queen', 'code', 'basil', 'time',
        'wonderland', 'doctor', 'kosher', 'pig', 'milk', 'business', 'meat', 'album', 'texas', 'badger',
        'silent', 'comedy', 'peter', 'john', 'gene', 'pleasure', 'fragment', 'page', 'copy', 'gallery',
        'body', 'detective', 'harp', 'staff', 'sword', 'piano', 'crumb', 'bread', 'sugar', 'elk',
        'rope', 'backpack', 'axe', 'cube', 'triangle', 'keyboard', 'computer', 'message', 'window', 'house',
        'train', 'potato', 'boat', 'treasure', 'opal', 'gold', 'cobalt', 'speaker', 'listener', 'car',
        'light', 'nebula', 'virus', 'skirt', 'shirt', 'pants', 'boots', 'brick', 'wood', 'wasp',

        'codex', 'strict', 'fish', 'intensity', 'flow', 'jungle', 'mountain', 'desert', 'tundra', 'bevel',
        'checkers', 'spots', 'stripes', 'clean', 'sparkles', 'fjord', 'wheel', 'fire', 'magma', 'ice',
        'snow', 'cave', 'painting', 'pencil', 'dance', 'phone', 'submarine', 'hydrogen', 'supernova', 'battery',
        'storm', 'calm', 'salsa', 'tomato', 'pepper', 'salt', 'vanilla', 'chocolate', 'blue', 'navy',
        'sky', 'ocean', 'whale', 'squid', 'plankton', 'orange', 'carrot', 'black', 'dust', 'magic',
        'pasta', 'cookie', 'panda', 'iron', 'brownie', 'spirit', 'elf', 'dwarf', 'goblin', 'shift',
        'canada', 'pine', 'thimble', 'button', 'crime', 'yolk', 'crystal', 'jet', 'toll', 'fine',
        'dirty', 'rough', 'diamond', 'jewel', 'penny', 'screw', 'globe', 'hammer', 'tool', 'log',
        'carbon', 'ash', 'skewer', 'plate', 'arm', 'leg', 'foot', 'tongue', 'bracket', 'dim',
        'dirt', 'mud', 'kelp', 'shower', 'lobby', 'elevator', 'tower', 'skyscraper', 'downtown', 'wasteland',

        'cloak', 'spell', 'wing', 'segment', 'bulb', 'towel', 'cable', 'twist', 'filament', 'membrane',
        'string', 'nothing', 'ring', 'adventure', 'kirby', 'sonic', 'mario', 'peach', 'cloud', 'river',
        'white', 'treat', 'water', 'pizza', 'casserole', 'chunk', 'gravy', 'slime', 'ooze', 'statue',
        'crest', 'rock', 'point', 'coin', 'hole', 'pit', 'tar', 'feather', 'bird', 'nest',
        'joint', 'fuzz', 'velcro', 'scissors', 'twine', 'paperclip', 'tetris', 'web', 'volcano', 'trench',
        'glass', 'box', 'crate', 'barrel', 'candle', 'clip', 'escalator', 'store', 'cache', 'tiger',
        'map', 'diagram', 'planet', 'moon', 'gemstone', 'sack', 'evolution', 'ceiling', 'pad', 'knob',
        'guitar', 'ninja', 'pirate', 'robot', 'gnome', 'hobbit', 'impossible', 'whistle', 'hop', 'monk',
        'gandalf', 'doom', 'android', 'data', 'enterprise', 'klingon', 'steam', 'valve', 'space', 'leaf',
        'pikachu', 'friend', 'celery', 'chicken', 'career', 'sculpture', 'discovery', 'museum', 'bitter', 'needle',

        'wallet', 'shaft', 'canoe', 'ivy', 'poem', 'russia', 'april', 'shaman', 'sultan', 'raisin',
        'cliff', 'glacier', 'network', 'firewall', 'hash', 'penguin', 'volume', 'power', 'acid', 'bass',
        'inbox', 'myth', 'human', 'alchemy', 'falcon', 'caramel', 'onion', 'escape', 'mirror', 'lion',
        'zero', 'boyfriend', 'distance', 'juice', 'valley', 'rocket', 'strange', 'evil', 'crypt', 'chasm',
        'diary', 'janitor', 'pack', 'ink', 'shipwreck', 'marble', 'granite', 'aqueduct', 'travel', 'puzzle',
        'tooth', 'canal', 'america', 'brazil', 'york', 'dipper', 'beans', 'hunt', 'watermelon', 'sushi',
        'basket', 'artichoke', 'mango', 'soup', 'cumin', 'nutmeg', 'peanut', 'walnut', 'strawberry', 'mulberry',
        'raspberry', 'blackberry', 'cherry', 'tangerine', 'trolley', 'cart', 'trousers', 'bicycle', 'girl', 'boy',
        'lava', 'dress', 'scarf', 'suspenders', 'loaf', 'rosemary', 'squirrel', 'envelope', 'origami', 'guardian',
        'brand', 'mushroom', 'cartridge', 'disc', 'photo', 'selfie', 'toothpick', 'knapsack', 'tartan', 'burlap',

        'pigeon', 'bottle', 'peace', 'music', 'trumpet', 'doggo', 'cheese', 'swiss', 'milkshake', 'cream',
        'pause', 'dinosaur', 'dollar', 'silver', 'platinum', 'smart', 'baseball', 'ginger', 'laundry', 'ruler',
        'king', 'jester', 'scepter', 'mess', 'laser', 'shark', 'scroll', 'notebook', 'dragon', 'hamster',
        'snake', 'chimera', 'pyramid', 'sphinx', 'otter', 'elephant', 'spruce', 'heavy', 'soft', 'mouse',
        'bison', 'bear', 'zebra', 'camel', 'bobcat', 'bluebird', 'crane', 'cricket', 'dingo', 'dolphin',
        'dragonfly', 'eagle', 'ferret', 'finch', 'flamingo', 'goat', 'sheep', 'goose', 'gorilla', 'hawk',
        'hedgehog', 'shovel', 'knight', 'prince', 'princess', 'peasant', 'hippo', 'jelly', 'human', 'koala',
        'lizard', 'lynx', 'llama', 'lobster', 'mammoth', 'flame', 'flood', 'giant', 'moose', 'newt',
        'oyster', 'parrot', 'panther', 'pelican', 'quail', 'rabbit', 'raccoon', 'pawn', 'salmon', 'sardine',
        'quartz', 'ruby', 'sapphire', 'emerald', 'jade', 'skunk', 'shrew', 'sloth', 'snail', 'swan',

        'toad', 'turkey', 'turtle', 'viper', 'weasel', 'wolf', 'brigade', 'worm', 'wombat', 'apple',
        'rose', 'apricot', 'plum', 'date', 'coconut', 'green', 'pink', 'purple', 'eggplant', 'lime',
        'lemon', 'blueberry', 'cranberry', 'grape', 'kiwi', 'wild', 'stump', 'bridge', 'canoe', 'happy',
        'melon', 'banana', 'cashew', 'monkey', 'lake', 'digital', 'salad', 'fruit', 'vegetable', 'dill',
        '365', '500', '1000', '2000', '5000', 'half', 'first', 'whole', 'quarter', 'endless',
        'mail', 'walk', 'trail', 'forest', 'path', 'canopy', 'tent', 'plastic', 'rubber', 'mine',
        'almond', 'pluto', 'jupiter', 'venus', 'mars', 'earth', 'neptune', 'star', 'party', 'fort',
        'grass', 'double', 'browse', 'arrow', 'boulder', 'hill', 'fluff', 'junk', 'thing', 'stuff',
        'carpet', 'floor', 'wall', 'trash', 'world', 'universe', 'start', 'finish', 'strong', 'alive',
        'kiss', 'love', 'safe', 'style', 'rain', 'bright', 'true', 'false', 'sunshine', 'tall',

        'facts', 'smile', 'face', 'grace', 'sweet', 'shine', 'funk', 'dawn', 'mind', 'flower',
        'float', 'eyes', 'night', 'simple', 'ground', 'color', 'idea', 'sound', 'rainbow', 'plan',
        'trust', 'free', 'city', 'feeling', 'lucky', 'option', 'next', 'stage', 'legend', 'force',
        'ribbon', 'gift', 'secret', 'pearl', 'trees', 'mystery', 'mouth', 'wrong', 'club', 'stand',
        'hope', 'weight', 'event', 'corner', 'disco', 'loud', 'drum', 'machine', 'again', 'wave',
        'future', 'past', 'sleep', 'dream', 'hard', 'vine', 'sign', 'young', 'bold', 'right',
        'burn', 'word', 'sink', 'sail', 'bloom', 'upgrade', 'control', 'victory', 'switch', 'heart',
        'climb', 'defend', 'touch', 'fall', 'follow', 'down', 'parry', 'shield', 'student', 'master',
        'teacher', 'professor', 'wizard', 'warrior', 'rogue', 'cake', 'dessert', 'oxygen', 'pilot', 'money',
        'bike', 'balloon', 'sausage', 'blank', 'wedding', 'girlfriend', 'husband', 'wife', 'christmas', 'pony',
        // ^ 800!
    ];
}