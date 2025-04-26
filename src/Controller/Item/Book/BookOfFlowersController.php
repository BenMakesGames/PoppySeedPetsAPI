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


namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/bookOfFlowers")]
class BookOfFlowersController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'bookOfFlowers/#/read');

        return $responseService->itemActionSuccess('# Book of Flowers

### Acacia

A flower that means "secret love"!
 
Scandalous!

I can\'t condone cheating, but I guess if you\'re gonna do it, I can\'t really stop you?

Have you considered an open relationship? Not that an open relationship is an excuse to be sneaky or dishonest! Honesty is always important, in any relationship!

### Bird\'s-foot Trefoil

This cruel flower represents REVENGE. If you\'ve been sending a lot of Acacias to someone, it might not be too surprising to receive a bouquet of bird\'s-foot trefoil...

Pretty cool name, though!

### Carnation

This is one of those popular flowers that has a bunch of meanings depending on its color. (Like roses! Ugh! (More on those later!))

Anyway, here\'s a few carnation color meanings:

##### Green
So, Wikipedia describes this simply as a secret symbol of Oscar Wilde\'s followers. You should [google green carnation oscar wilde](https://duckduckgo.com/?q=green+carnation+oscar+wilde). I learned a lot.

##### White
Innocence; pure love; faithfulness.

##### Pink
A woman\'s love; a mother\'s love.

Hm, hold on a sec * skims over list of flower meanings * Yeah, hey: where\'s the flower for a man\'s love? a FATHER\'S love? "Oh, men are supposed to be \'strong\', and it\'s not \'strong\' to express squishy emotions like love," is that it? Thanks FLOWERS; toxic masculinity _really_ needed your help.

Sorry, sorry, I got a little worked up there.

Pink carnations are nice, and you should 100% still totally grow and enjoy and smell and use them.

##### Purple
CAPRICIOUSNESS?! Oh man. That\'s a great word. Maybe not the best message to send to... most people... but a great word!

##### Mauve
Dreams of fantasy? Oh my. These are starting to get a little steamy. Let\'s move on to the next flower.

### Cowslip

Um.

Let\'s.

Just keep moving.

### Coriander

Coriander is a GREAT spice. Also, its flower means... lust?! I mean... I\'m not necessarily _against_ lust, I guess. I lust after coriander, that\'s for sure! Pour some in my curry; pour some in my pants - why not? AIN\'T NOTHING WRONG WITH LOVE BETWEEN A MAN AND A CULINARY SPICE!

### Cypress

Death.

_Mourning._

_DESPAIR._

Well that killed the mood.

### Love Lies Bleeding

I like how every now and again, some creature of the natural world ends up with some super-messed-up name, and you\'re like "so the biologist who discovered this bug called it a \'pleasing fungus beetle\'? Okay, then. And this wouldn\'t happen to be the same biologist who named the \'satanic leaf-tailed gecko\', would it?"

To be fair, that gecko does look _cartoonishly_ evil.

Hm? What? Flowers? Oh yes: "love lies bleeding".

YEP.

THAT\'S A FLOWER NAME.

Oh, its _meaning!_ Yes, sorry, I almost forgot...

HOPELESSNESS.

I mean, were you expecting any differently, with a name like that?

Also, so, these names have been pretty spot-on, then, yeah? Love Lies Bleeding = hopeless; satanic whatever-gecko = freaky red eyes? But..."pleasing fungus beetle"? I don\'t-- I don\'t get it.

### Oak Leaf

A symbol of strength, apparently, though I usually think of oak leaves as being scattered dead all over the ground, having _not_ had the strength to hold on to their trees. I mean, like, if an oak leaf symbolizes strength, then the pine needles of a douglas fir must be off the fuckin\' charts! Herculean!

### Pansy

"There\'s rosemary, that\'s for remembrance. Pray you, love, remember. And there is pansies, that\'s for thoughts." ~Ophelia

### Red Tulip

UNDYING LOVE. _Passion._ Perfect love...

Sometimes one flower will have meanings that are so super-different, I feel like I can see how the context of the situation would let you know what they mean, in the same way that I can say "that plane is a bit larger than I expected", and I might be talking about math, airplanes, or carpentry, but whatever the case, you won\'t be confused in the moment, because either we\'re scribbling on some graph paper, looking out the window in an airport, or covered in sawdust (and probably only one of the three).

If someone gave me a red tulip, I\'d be like "so do you wanna bang, or get married; I can\'t tell. Or is it both?? What\'s going on here?"

### Rose

Roses are troublingly popular, leading to a wide variety of meanings depending on the color of one. Other flowers come in a variety of colors too, roses! What\'s so special about you!?

Eh. Whatever. History, or something, I guess.

##### Red
True love.

##### Blue

Mystery. ATTAINING THE IMPOSSIBLE.

Whoa.

Okay, that\'s kinda\' bad-ass.

##### Black
Death. HATRED... but also rejuvenation? Quite a combo, but it doesn\'t _not_ make sense, I guess??

##### Pink
Grace.

##### Dark Pink
Gratitude.

What, not, like... "dark grace"?

##### Orange
Desire. Passion.

I didn\'t know roses came in orange, but I guess I didn\'t know they came in blue or black, either...

Actually, blue roses were in that anime, uh... BLOOD+! The evil vampire lady was super into blue roses, I think? And had an _amazing_ theme song.

Ah, sorry, I\'m rambling again...

##### Thornless
Love at first sight.

Also, wtf: not a color??

### Rosemary

"There\'s rosemary, that\'s for remembrance--

Wait, didn\'t we already do this, for one of the other flowers\' entries? * scrolls up *

Ah, yes: pansy.

Well then.

...

W-- what?

I have a computer so I don\'t HAVE to remember this stuff! We invented paper, then the printing press, and now we have computers and the internet! I\'d be a fool NOT to take advantage of these!

### Star Jasmine

This one has made me really curious about how the meanings of flowers might vary across different countries and cultures. I mean, the whole flower meaning thing that this book is based on was totally a Victorian/English thing, BUT: this flower has specific significance in Hinduism!

The Hindu gods in general really enjoy flowers.

Why?

THEY SMELL GOOD! Obviously!
 
Stop interrupting me.

Anyway, so floral offerings are really common. Vishnu, in particular, is all about those flower offerings, and the star jasmine - due to its crazy-pure white color - is, like, a _particular_-particular fave. So this flower gets used for all kinds of things, and is considered an ESSENTIAL part of a marriage ceremony. One ceremony has the bride place two garlands of star jasmine on the groom, who then takes one of the garlands and places it on the bride.
 
ADORABLE.

I love it.

And so does Vishnu, apparently, so. You know.

Star jasmine.

Think about it.

### Violet

Another flower whose meaning changes based on its color. Fortunately, it\'s less popular than carnations and roses, so the list is shorter this time:

##### Blue
Faithfulness.

##### Purple
Love between two women.

##### White
Modesty.

I like how that list, like, starts and ends on pretty similar notes, but has this twist in the middle. Or maybe it\'s like a short story, written with only six words across three "sentences"? Whatever the case, I\'m here for it.

### Viscaria

If you\'re too embarrassed to ask someone to dance, send them this flower.

Although then you have to work up the nerve to ask someone to bring a Viscaria to your prospective dance party, and they\'re gonna be like "what the hell is a \'Viscaria\'?" and you\'ll be all "you work at a _florist\'s!_ How do you not know what a Viscaria is!? Also, you shouldn\'t swear at work. ALSO-ALSO, LOOK: if you don\'t have one, just tell me, because this song is only going to be playing for-- he hung up. He hung up on me! I can\'t believe this!" and just, like, _throw_ the phone acro--

What?

Fine, fine: next flower.

Gosh.

No patience.

OKAY, I\'M GOI--

### Witch-hazel

Wikipedia simply says "a magic spell". W-- what am I meant to make of that? What does it mean to give someone an item that symbolizes "a magic spell"? Or is it saying that a witch-hazel flower is itself the physical manifestation of a magic spell?

I tried googling around for more, but all I learned was that it blooms in late February or March, so it can be nice in a garden, to get some flowers showing up early.

I guess that\'s kind of magical??

### Wheat

It\'s hard to say which is the lazier thing to give to someone: a _piece of wheat_, or an _oak leaf_. Like, for real, if you went up to your friend, and all super-embarrassedly handed them _a piece of wheat_... I mean... right??

BUT: fine, let\'s assume the recipient isn\'t so thrown off by your grainy offering that they\'re unable to do anything but look at it literally (and at you as a crazy person)... quite an assumption, BUT OKAY, I\'M WILLING TO PLAY PRETEND. Well, in this highly-contrived situation, that person _might_ recall - assuming they ever knew to begin with - that wheat is a symbol of wealth and prosperity, and be quite flattered by your offering!

What a nice gift!
 
Although really, thinking about it, if that person\'s initial response _is_ "omg, a symbol a wealth and prosperity; thank you!" that person must be the best kind of nerd imaginable, and you should probably formalize some kind of life-long partnership at your earliest convenience.
');
    }
}
