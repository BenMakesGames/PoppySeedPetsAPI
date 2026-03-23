import { Component, OnInit, ViewChild } from '@angular/core';
import {UserSessionService} from "../../service/user-session.service";
import { ThemeService } from "../../module/shared/service/theme.service";
import { BuiltInThemeSerializationGroup } from "../../model/built-in-theme.serialization-group";

@Component({
    templateUrl: './register.component.html',
    styleUrls: ['./register.component.scss'],
    standalone: false
})
export class RegisterComponent implements OnInit {
  pageMeta = { title: 'Sign Up!', song: 'the-ocean' };

  readonly availablePets = [
    {
      name: 'Desikh',
      image: 'monotreme/desikh'
    },
    {
      name: 'Roundish',
      image: 'mammal/roundish',
    },
    {
      name: 'Cotton Candy',
      image: 'elemental/cotton-candy',
    },
    {
      name: 'Chickie',
      image: 'bird/chickie',
    },
    {
      name: 'Mole',
      image: 'mammal/mole',
    },
    {
      name: 'Oft',
      image: 'bird/odd-flying-thing',
    },
    {
      name: 'Triangle',
      image: 'elemental/triangle',
    },
    {
      name: 'Fish with Legs',
      image: 'amphibian/ba-ha',
    },
    {
      name: 'Mousie',
      image: 'mammal/much-cuter-mousie',
    },
    {
      name: 'Mushroom',
      image: 'fungus/mushroom',
    },
    {
      name: 'Bunny',
      image: 'mammal/bunny',
    },
    {
      name: 'Peacock',
      image: 'bird/bi-colored-peacock',
    },
    {
      name: 'A.D.',
      image: 'elemental/a-d',
    },
    {
      name: 'Sneqo',
      image: 'lizard/sneqo',
    },
    {
      name: 'Bulbun',
      image: 'mammal/bulbun',
    },
    {
      name: 'Amalgam',
      image: 'slime/amalgam',
    },
    {
      name: 'Red Piper',
      image: 'bird/red-piper',
    },
    {
      name: 'Duster-tailed Fox',
      image: 'mammal/duster-tail-fox',
    }
    // only these 18! don't add anymore!
  ];

  readonly petNames = [
    'Aalina', 'Aaron', 'Abrahil', 'Addy', 'Aedoc', 'Aelfric', 'Aimery', 'Alain', 'Alda', 'Aldreda', 'Aldus',
    'Alienora', 'Aliette', 'Amée', 'Amis', 'Amphelise', 'Arlotto', 'Artaca', 'Auberi', 'Aureliana',

    'Batu', 'Belka', 'Berislav', 'Bezzhen', 'Biedeluue', 'Blicze', 'Bogdan', 'Bogdana', 'Bogumir', 'Bradan',
    'Bratomil',

    'Cateline', 'Ceinguled', 'Ceri', 'Ceslinus', 'Chedomir', 'Christien', 'Clement', 'Coilean', 'Col', 'Cynbel',
    'Cyra', 'Czestobor',

    'Dagena', 'Dalibor', 'Denyw', 'Dicun', 'Disideri', 'Dmitrei', 'Dragomir', 'Dye',

    'Eda', 'Eileve', 'Elena', 'Elis', 'Elric', 'Emilija', 'Enguerrand', 'Enim', 'Enynny', 'Erasmus', 'Estienne',
    'Eve',

    'Felix', 'Fiora', 'Firmin', 'Fluri', 'Frotlildis',

    'Galine', 'Garnier', 'Garsea', 'Gennoveus', 'Genoveva', 'Geoffroi', 'Gidie', 'Giliana', 'Godelive', 'Gomes',
    'Gosse', 'Gubin', 'Guiscard', 'Gwennan',

    'Hamon', 'Hamon', 'Hopcyn', 'Hunfrid',

    'Ibb', 'Idzi',

    'Jadviga', 'Jehanne', 'Jocosa', 'Josse', 'Jurian',

    'Kaija', 'Kain', 'Kazimir', 'Kima', 'Kinborough', 'Kint', 'Kirik', 'Klara', 'Kryspin',

    'Larkin', 'Leodhild', 'Leon', 'Levi', 'Lorencio', 'Lowri', 'Lucass', 'Ludmila',

    'Maccos', 'Maeldoi', 'Magdalena', 'Makrina', 'Malik', 'Margaret', 'Marsley', 'Masayasu', 'Mateline',
    'Mathias', 'Matty', 'Maurifius', 'Meduil', 'Melita', 'Meoure', 'Merewen', 'Milian', 'Millicent', 'Mold',
    'Molle', 'Montgomery', 'Morys', 'Muriel',

    'Nascimbene', 'Newt', 'Nicholina', 'Nilus', 'Noe', 'Noll', 'Nuño',

    'Onfroi', 'Oswyn',

    'Paperclip', 'Perkhta', 'Pesczek', 'Pridbjørn',

    'Radomil', 'Raven', 'Regina', 'Reina', 'Rimoete', 'Rocatos', 'Rostislav', 'Rozalia', 'Rum', 'Runne', 'Ryd',

    'Saewine', 'Sancha', 'Sandivoi', 'Skenfrith', 'Sulimir', 'Sunnifa', 'Sybil',

    'Taki', 'Talan', 'Tede', 'Temüjin', 'Tephaine', 'Tetris', 'Tiecia', 'Timur', 'Tomila', 'Toregene', 'Trenewydd',

    'Úna', 'Usk',

    'Vasilii', 'Venceslaus', 'Vitseslav', 'Vivka',

    'Wilkin', 'Wilmot', 'Wrexham', 'Wybert', 'Wymond',

    'Ximeno',

    'Yaromir', 'Yrian', 'Ysabeau', 'Ystradewel',

    'Zofija', 'Zuan', 'Zygmunt'
  ];

  readonly adjectives = [
    'Smooth', 'Curly', 'Dashing', 'The', 'OP', 'Hot', 'Twisted', 'Tiny', 'Iridescent', 'Mighty',
    'Speedy', 'Peaceful', 'Irate', 'Long', 'Short', 'Wild', 'Pithy', 'Punchy', 'Wise', 'Naïve',
    'Clever', 'Handsome', 'Sneaky', 'Outrageous', 'Wild', 'Fuzzy', 'Prismatic', 'Wholesome',
    'Wily', 'Uncouth', 'Rubicund', 'Effulgent', 'Quixotic', 'Recondite', 'Voracious', 'Subtle',
    'Sanguine', 'Luminous', 'Quaint', 'Intangible', 'Rustic', 'Whimsical', 'Illogical', 'Rad',
  ];

  readonly nouns = [
    'Cucumber', 'Mango', 'Scout', 'Ranger', 'Strawberry', 'Doggo', 'Rainbow', 'Jelly', 'Cat',
    'Banana', 'Potato', 'Oak', 'Monster', 'Human', 'Robot', 'Spinach', 'Broccoli', 'Asparagus',
    'Breakfast', 'Hammer', 'Joker', 'Elf', 'Zebra', 'Plum', 'Penguin', 'Tulip', 'Lantern',
    'Ocelot', 'Lagoon', 'Pebble', 'Smoothie', 'Cookie', 'Toupee', 'Scrunchie', 'Pickle',
    'Pillow', 'Uvula', 'Ukulele'
  ];

  readonly secondaryNouns = [
    'Smoothie', 'Trouble', '999', 'Zero', 'Alpha', 'Mélange', 'Hairdo', 'Love', 'Villain', 'Hero',
    'Paradox', 'Runner', 'Bedtime', 'Hole', 'Face', 'Box', 'Fan', 'Time', 'Space', 'Wizardry',
    'Cliché', 'Seeker', 'Dreamer', 'House',
  ];

  step = 1;

  cookiesAreOkay = false;
  iPlayNice = false;
  iCanInternet = false;

  error: string;
  step2Dialog: string;
  loading = false;
  theme: BuiltInThemeSerializationGroup|undefined;
  name: string;
  email: string;
  passphrase: string;
  passphraseType = 'password';
  petName: string;
  selectedPet = 0;
  colorA: string;
  colorB: string;
  petColors = { colorA: '', colorB: '' };

  readonly colorPairs = [
    [ '4d7ca1', '86b2de' ],
    [ '9d5535', 'c68f27' ],
    [ 'f1067f', 'ffffff' ],
    [ '67a75b', 'd4583b' ],
    [ '32c4b0', 'd65ecb' ],
    [ '834d4d', '56253d' ],
    [ 'f3d200', '009307' ],
  ];

  @ViewChild('passphraseInput', { 'static': true }) passphraseInput;

  constructor(private userSession: UserSessionService, private themeService: ThemeService) {
    this.selectedPet = Math.floor(Math.random() * this.availablePets.length);

    const colors = this.colorPairs[Math.floor(Math.random() * this.colorPairs.length)];
    this.colorA = colors[0];
    this.colorB = colors[1];
    this.updatePetColors();
    this.theme = ThemeService.Themes[0];
  }

  ngOnInit() {
    this.themeService.setTheme(this.theme);
  }

  updatePetColors()
  {
    this.petColors = { colorA: this.colorA, colorB: this.colorB };
  }

  goToStep2(dialog: string)
  {
    this.step2Dialog = dialog;
    this.error = null;
    this.step = 2;
  }

  doEnterName()
  {
    this.name = this.name ? this.name.trim() : '';

    if(this.name.length < 2 || this.name.length > 30)
    {
      this.error = 'Your name should be at least 2 characters long, and no longer than 30.';
      return;
    }

    if(!this.isISO88591(this.name))
    {
      this.error = 'Your name contains some awfully strange characters. Many accented characters ARE allowed, but not all, and most characters from non-Roman alphabets and character sets are not allowed. (Sorry!)';
      return;
    }

    this.error = null;
    this.step = 3;
  }

  doCustomizePet()
  {
    this.step = 4;
  }

  doNamePet()
  {
    this.petName = this.petName ? this.petName.trim() : '';

    if(this.petName.length < 2 || this.petName.length > 30)
    {
      this.error = 'Your pet\'s name should be at least 2 characters long, and no longer than 30.';
      return;
    }

    if(!this.isISO88591(this.petName))
    {
      this.error = 'Your pet\'s name contains some awfully strange characters. Many accented characters are allowed, but not all, and most characters from non-Roman alphabets and character sets are not allowed. (Sorry!)';
      return;
    }

    this.error = null;
    this.step = 5;
  }

  doRandomizePassphrase()
  {
    this.passphrase =
      this.passphraseWords[Math.floor(Math.random() * this.passphraseWords.length)] + ' ' +
      this.passphraseWords[Math.floor(Math.random() * this.passphraseWords.length)] + ' ' +
      this.passphraseWords[Math.floor(Math.random() * this.passphraseWords.length)] + ' ' +
      this.passphraseWords[Math.floor(Math.random() * this.passphraseWords.length)]
    ;
    this.passphraseType = 'text';

    setTimeout(() => {
      this.passphraseInput.nativeElement.select();
      this.passphraseInput.nativeElement.setSelectionRange(0, this.passphrase.length);
    }, 0);
  }

  doChangePassword()
  {
    if(this.passphrase === '')
      this.passphraseType = 'password';
  }

  doRandomizeName()
  {
    const noun = this.nouns[Math.floor(Math.random() * this.nouns.length)];

    if(Math.random() < 0.5)
      this.name = noun + ' ' + this.secondaryNouns[Math.floor(Math.random() * this.secondaryNouns.length)];
    else
      this.name = this.adjectives[Math.floor(Math.random() * this.adjectives.length)] + ' ' + noun;
  }

  doRandomizePetName()
  {
    this.petName = this.petNames[Math.floor(Math.random() * this.petNames.length)];
  }

  doGoBack(step: number)
  {
    this.step = step;
    this.error = null;
  }

  isISO88591(s: string): boolean
  {
    return !/[^\u0000-\u00ff]/g.test(s);
  }

  doRegister()
  {
    if(this.loading) return;

    this.email = this.email ? this.email.trim() : '';
    this.passphrase = this.passphrase ? this.passphrase : '';

    if(this.passphrase.length < 12)
    {
      this.error = 'Your passphrase needs to be at least 12 characters. "Special characters" are not required - use a short sentence, or a password manager.';
      return;
    }

    if(!this.email.match(/^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/))
    {
      this.error = 'Hm... that doesn\'t look like an e-mail address. (It\'s okay to use a fake e-mail address, but it\'s gotta\' at least look like an e-mail address!)';
      return;
    }

    if(this.email.endsWith('@poppyseedpets.com') || this.email.endsWith('.poppyseedpets.com'))
    {
      this.error = 'Ah, sorry: poppyseedpets.com e-mail addresses are reserved for employees.';
      return;
    }

    this.loading = true;
    this.error = null;

    this.userSession.register(
      this.theme,
      this.name,
      this.email,
      this.passphrase,
      this.petName,
      this.availablePets[this.selectedPet].image,
      this.colorA,
      this.colorB
    ).subscribe({
      next: () => {
        this.userSession.showTutorial = true;
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  passphraseWords = [
    "1000", "1001", "2000", "3000", "5000", "9000", "9999",
    
    "abrupt", "achievement", "acid", "acorn", "actual", "adult", "adventure", "affiliate", "again", "agreement",
    "alarm", "album", "alchemy", "algebra", "alive", "almond", "alone", "amused", "anchor", "android", "apple",
    "apricot", "april", "aqueduct", "armor", "arrow", "artichoke", "asleep", "attack", "awake", "award",
    
    "backpack", "badger", "banana", "bank", "barrel", "baseball", "basil", "basket", "bass", "battery", "beach",
    "beans", "bear", "beaver", "bevel", "bicycle", "bill", "bird", "bison", "bitter", "black", "blackberry", "blood",
    "bloom", "blue", "blueberry", "bluebird", "boat", "bobcat", "body", "bold", "book", "boots", "bottle", "boulder",
    "boxy", "boyfriend", "boyish", "bracket", "brand", "brazil", "bread", "brick", "bridge", "brigade", "bright",
    "britain", "brother", "brown", "brownie", "browse", "budget", "buffalo", "bulb", "burlap", "burn", "business",
    "butterfly", "button",
    
    "cable", "cache", "cafe", "calm", "camel", "campground", "canal", "candle", "candy", "canoe", "canopy", "captain",
    "caramel", "carbon", "career", "career", "carpet", "carrot", "cart", "cartoon", "cartridge", "cashew", "casserole",
    "cave", "ceiling", "celery", "center", "chamber", "character", "chasm", "checkers", "cheese", "cherry", "chicken",
    "chimera", "china", "chocolate", "chunk", "circuit", "city", "claims", "clean", "cliff", "climb", "clip", "cloak",
    "cloud", "club", "cobalt", "coconut", "code", "codex", "coin", "collection", "color", "colorado", "comedy",
    "comic", "computer", "continent", "control", "cookie", "copy", "corner", "cranberry", "crane", "crate", "cream",
    "creek", "crest", "cricket", "crime", "crumb", "crypt", "crystal", "cube", "culture", "cumin",

    "dance", "danger", "data", "date", "david", "dawn", "decline", "deer", "defend", "desert", "detective", "diagram",
    "diamond", "diary", "digital", "dill", "dimly", "dingo", "dinosaur", "dipper", "dirt", "dirty", "disc", "disco",
    "discovery", "distance", "district", "doctor", "doggo", "dollar", "dolphin", "doom", "double", "down", "downtown",
    "dragon", "dragonfly", "dream", "dress", "drum", "dust", "dwarf",

    "eagle", "earth", "eclipse", "edge", "eggplant", "eggy", "elephant", "elevator", "eleven", "elite", "elixir",
    "emerald", "empty", "endless", "enterprise", "envelope", "environment", "escalator", "escape", "essay", "event",
    "evil", "evolution", "exhale", "eyes",

    "face", "facility", "facts", "falcon", "fall", "false", "family", "father", "feather", "feature", "feeling",
    "ferret", "festival", "field", "filament", "finch", "fine", "finish", "fire", "firewall", "first", "fish", "fjord",
    "flame", "flamingo", "float", "flood", "floor", "flow", "flower", "fluff", "flute", "follow", "foot", "footballer",
    "force", "forest", "fort", "fragment", "free", "friend", "fruit", "funk", "future", "fuzz",

    "gallery", "gandalf", "garden", "gemstone", "gene", "general", "germs", "giant", "gift", "ginger", "girl",
    "glacier", "glass", "globe", "gnome", "goat", "goblin", "gold", "gong", "goose", "gorilla", "grace", "granite",
    "grape", "graph", "grass", "gravy", "green", "ground", "guard", "guardian", "guitar",

    "half", "hammer", "hamster", "happy", "hard", "harp", "hash", "hawk", "heart", "heavy", "hedgehog", "hemlock",
    "hill", "hippo", "history", "hobbit", "hole", "honeydew", "hope", "hopeful", "horse", "house", "household",
    "hovercraft", "human", "human", "hunt", "hydrogen",

    "iceberg", "icicle", "idea", "illusion", "image", "immune", "impact", "implied", "impossible", "inbox", "include",
    "infinity", "infant", "inky", "insect", "instant", "integer", "intensity", "irksome", "iron", "island", "ivory",

    "jacket", "jade", "janitor", "jargon", "jaunty", "jelly", "jester", "jewel", "jigsaw", "john", "joint", "jolly",
    "joker", "juice", "jump", "jungle", "junk", "jupiter", "justice",

    "karate", "kelp", "keyboard", "kidney", "kimchi", "king", "kingdom", "kirby", "kiss", "kitten", "kiwi", "klingon",
    "knapsack", "knight", "knob", "koala", "kosher", "kudzu",

    "lake", "largest", "laser", "laugh", "laundry", "lava", "leaf", "legend", "lemon", "library", "license",
    "lifestyle", "light", "lime", "limit", "linger", "litter", "lion", "listener", "lizard", "llama", "loaf", "lobby",
    "lobster", "logic", "loud", "love", "lucky", "lumpy", "luxury", "lynx",

    "machete", "machine", "magic", "magma", "maiden", "mail", "mammoth", "mango", "manor", "maps", "marble", "mario",
    "market", "mars", "meat", "melon", "member", "membrane", "mess", "message", "mile", "milk", "milkshake", "mind",
    "mine", "mirror", "mixer", "monday", "monk", "monkey", "moon", "moose", "mountain", "mouse", "mouth", "movement",
    "movie", "muddy", "mulberry", "museum", "mushroom", "music", "mystery", "myth",

    "narrative", "navy", "nebula", "needle", "neptune", "nest", "nettle", "network", "newt", "next", "nickle", "nifty",
    "night", "ninja", "noisy", "noodle", "north", "notebook", "nothing", "novice", "number", "nutmeg", "nylon",

    "oath", "oblong", "ocean", "octopus", "octagon", "officer", "often", "olympics", "onion", "ooze", "opal", "opera",
    "operator", "option", "orange", "origami", "otter", "outline", "overdue", "oxygen", "oyster",

    "pack", "packer", "page", "painting", "panda", "panther", "pants", "paperclip", "parent", "parrot", "party",
    "past", "pasta", "path", "pause", "pawn", "payment", "peace", "peach", "peacock", "peanut", "pearl", "peasant",
    "pelican", "pencil", "penguin", "penny", "pepper", "performance", "peter", "phone", "photo", "piano", "picnic",
    "pigeon", "piglet", "pikachu", "pine", "pink", "pirate", "pit", "pizza", "plan", "planet", "plankton", "plastic",
    "plate", "platinum", "play", "pleasure", "plum", "pluto", "poem", "point", "police", "policy", "potato", "power",
    "prince", "princess", "processor", "product", "public", "purple", "puzzle", "pyramid",

    "quail", "quake", "quarter", "quartz", "queen", "quest", "quill", "quirky", "quote",

    "rabbit", "raccoon", "rain", "rainbow", "raisin", "random", "raspberry", "rave", "receptor", "rescue", "research",
    "rhyme", "ribbon", "right", "ring", "river", "road", "roasted", "robot", "rock", "rocket", "rook", "room", "rope",
    "rose", "rosemary", "rough", "route", "rubber", "ruby", "ruler", "runway",

    "sack", "safe", "sail", "salad", "salmon", "salsa", "salt", "sapphire", "sardine", "saturday", "scarf", "scepter",
    "science", "scissors", "scott", "scout", "screw", "scroll", "sculpture", "secret", "segment", "selfie", "seven",
    "shaft", "shaman", "shark", "sheep", "shift", "shine", "shipwreck", "shirt", "shovel", "shower", "shrew", "sign",
    "silent", "silver", "simple", "sink", "sixteen", "skewer", "skirt", "skunk", "skyscraper", "sleep", "slime",
    "sloth", "smart", "smile", "snail", "snake", "snow", "society", "soft", "software", "sonata", "sonic", "sorcery",
    "sound", "soup", "source", "space", "sparkles", "speaker", "species", "spell", "sphinx", "spider", "spirit",
    "spots", "spruce", "squid", "squirrel", "staff", "stage", "stand", "star", "start", "statue", "steam", "store",
    "storm", "story", "strange", "strawberry", "street", "strict", "string", "stripes", "strong", "studio", "stuff",
    "stump", "style", "submarine", "sugar", "sultan", "sunshine", "supernova", "sushi", "suspenders", "swan", "sweet",
    "swiss", "switch", "sword", "system",

    "tabby", "tall", "tangerine", "tartan", "team", "temple", "tent", "tetris", "thimble", "thing", "thinky", "three",
    "tiger", "time", "titan", "toad", "toll", "tomato", "tongue", "tool", "tooth", "toothpick", "touch", "tournament",
    "towel", "tower", "tracy", "trail", "train", "trash", "travel", "treasure", "treat", "trees", "trench", "triangle",
    "trolley", "trousers", "true", "trumpet", "trust", "tundra", "turkey", "turtle", "twine", "twist",

    "uncanny", "uncle", "unity", "undo", "universe", "unkempt", "untidy", "unwise", "upgrade", "uplifting", "uranium",
    "utensil",

    "valley", "valve", "vanilla", "vegetable", "velcro", "venus", "vertex", "vibes", "victory", "vine", "viper",
    "virus", "void", "volcano", "volume", "vortex", "vowel",

    "waldo", "walk", "wall", "wallet", "walnut", "warp", "wasp", "wasteland", "water", "watermelon", "wave",
    "weakness", "weasel", "webbed", "weight", "whale", "wheat", "wheel", "whistle", "white", "whole", "wild",
    "wildflower", "window", "wing", "wire", "wolf", "wombat", "wonderland", "wood", "word", "world", "worm", "wrong",

    "yacht", "year", "yellow", "yielding", "yogurt", "yolk", "york", "young", "yowling",

    "zebra", "zero", "zinc", "zipper", "zombie", "zone", "zoom",
  ];

}
