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

#[Route("/item/poems")]
class PoemsController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'poems/#/read');

        return $responseService->itemActionSuccess(<<<EOMD
# ⤮

Collected poems by various authors.

## ALMOST!

Within my reach!<br />
I could have touched!<br />
I might have chanced that way!<br />
Soft sauntered through the village,<br />
Sauntered as soft away!<br />
So unsuspected violets<br />
Within the fields lie low,<br />
Too late for striving fingers<br />
That passed, an hour ago.

## from The shepherd's Sirena

When she looks out by night<br />
&nbsp;&nbsp;&nbsp;the stars stand gazing<br />
Like comets to our sight<br />
&nbsp;&nbsp;&nbsp;fearfully blazing<br />
As wondering at her eyes<br />
&nbsp;&nbsp;&nbsp;with their much brightness<br />
Which so amaze the skies<br />
&nbsp;&nbsp;&nbsp;dimming their lightness;<br />
The raging tempests are calm<br />
&nbsp;&nbsp;&nbsp;when she speaketh,<br />
Such most delightsome balm<br />
&nbsp;&nbsp;&nbsp;from her lips breaketh.<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;On thy bank<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;In a rank<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Let thy swans sing her<br />
And with their music<br />
&nbsp;&nbsp;&nbsp;along let them bring her. . . .

## HINTS FOR ETIQUETTE III

To use a fork with your soup, intimating at the same time to your hostess that you are reserving the spoon for the beefsteaks, is a practice wholly exploded.

## HINTS FOR ETIQUETTE VII

We do not recommend the practice of eating cheese with a knife and fork in one hand, and a spoon and wine-glass in the other; there is a kind of awkwardness in the action which no amount of practice can entirely dispel.

## one April dusk the

one April dusk the<br />
sallow street-lamps were turning<br />
snowy against a west of robin’s egg blue when<br />
i entered a mad street whose

mouth dripped with slavver of<br />
spring<br />
chased two flights of squirrel-stairs into<br />
a mid-victorian attic which is known as<br />
O ΠΑΡΘΕΝΩΝ<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;and having ordered<br />
yaoorti from<br />
Nicho’<br />
settled my feet on the

ceiling inhaling six divine inches<br />
of Haremina&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in<br />
the thick of the snick-<br />
er of cards and smack of back-

gammon boards i was aware of an entirely<br />
dirty circle of habitués their<br />
faces like cigarettebutts, chewed<br />
with disdain,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;lead by a Jumpy

Tramp who played each<br />
card as if it were a thunderbolt red-<br />
hot&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;peeling<br />
off huge slabs of a fuzzy

language with the aid of an exclamatory<br />
tooth-pick<br />
And who may that<br />
be i said exhaling into

eternity as Nicho’ laid<br />
before me bread<br />
more downy than street-lamps<br />
upon an almostclean

plate<br />
“Achilles”<br />
said<br />
Nicho’

“and did you perhaps wish also shishkabob?”

## THE MOUSE'S TAIL

<pre>
We lived beneath the mat
    Warm and snug and fat
        But one woe, and that
                    was the cat!
                      To our joys
                          a clog. In
                        our eyes a
                    fog, On our
                  hearts a log
                Was the dog!
              When the
            cat’s away,
          Then
      the mice
        will
          play,
            But, alas!
              one day; (So they say)
                  Came the dog and
                      cat, Hunting
                              for a
                            rat,
                        Crushed
                    the mice
                all flat,
            Each one,
          as he sat,
            Under-
              neath
                the mat,
                Warm &
                  snug
                & fat.
              Think
              of
          that!
</pre>

## THE OUTLET

My river runs to thee:<br />
Blue sea, wilt welcome me?

My river waits reply.<br />
Oh sea, look graciously!

I'll fetch thee brooks<br />
From spotted nooks, —

Say, sea,<br />
Take me!

## this wind is a Lady with

this wind is a Lady with<br />
bright slender eyes(who

moves)at sunset<br />
and who—touches—the<br />
hills without any reason

(i have spoken with this<br />
indubitable and green person “Are<br />
You the wind?” “Yes” “why do you touch flowers<br />
as if they were unalive,as

if They were ideas?” “because,sir<br />
things which in my mind blossom will<br />
stumble beneath a clumsiest disguise,appear<br />
capable of fragility and indecision

—do not suppose these<br />
without any reason and otherwise<br />
roses and mountains<br />
different from the i am who wanders

imminently across the renewed world”<br />
to me said the)wind being A lady in a green<br />
dress,who;touches:the fields<br />
(at sunset)

EOMD);
    }
}