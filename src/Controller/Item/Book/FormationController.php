<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item/formation")
 */
class FormationController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/read", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'formation/#/read');

        return $responseService->itemActionSuccess('# Formation

<p style="text-align:center">by Ben Hendel-Doying</p>

<blockquote style="font-style:italic">"... Quantities are derived from measurement, calculations are derived from
quantities, comparisons are derived from calculations, and victories are derived
from comparisons... this is formation."<br>&mdash; Sun Tzu\'s <cite>The Art of War</cite></blockquote>

### Summary

Formation is a two-player strategy card game.
Success depends on knowing both when and where to play your cards.

### Equipment Needed

You will need a standard 52-card deck and one Joker.

### How to Play

Divide the deck into two decks, one that contains Diamonds and Hearts, and another that
contains Clubs and Spades. Set the Joker aside for a moment.

Decide who will go first. The player that goes first plays as Black (Clubs and Spades),
and the other player plays Red (Diamonds and Hearts).

Each player shuffles his or her given deck, places it face-down nearby, and draws its top five cards into his or her hand.

Place the Joker between the two players.

Starting with whoever goes first, each player take turns performing the following steps, in order:

1. Flip over the card you played during your last turn so that it is face up.
2. Check for casualties (described below).
3. Draw a card from your deck, if one is available.
4. Play a card from your hand, if available, face-down, adjacent to any other card.

During each player\'s first turn that player will not perform steps 1 or 2.

### Ending the Game

The game is over when the following conditions are met:
* A player has ended his or her turn.
* Both players have no cards left in their hands.
* There are no face-down cards in play.

There will be a couple turns near the end of
the game in which neither player has cards to play, but cards need to be flipped over. Make sure
to flip these over and check for casualties in the proper order.

Once the game is over, each player totals the values of the cards they killed. The
player with the highest total wins. Ace is low (value of 1).

### Casualties

On the second step of each player\'s turn, you must check cards for "casualties" - cards that have been killed in battle.

For each card, check if it is killed:

1. Determine the total value of all the orthogonally-adjacent enemy cards.
2. Determine the total value of the card in question and all orthogonally-adjacent cards *of the same suit*.
3. If the sum of the enemy cards is larger, then the card in question is killed.

The following rules also apply:

* Ace is low (value of 1).
* Face-down cards do not contribute to either attack or defense.
* Don\'t remove cards that have died until you\'ve checked all of their attack options as well. *All cards attack simultaneously*. It\'s possible for two or more cards to kill each other simultaneously.
* The Joker does not aid in defense, does not attack, and cannot be killed. It is a neutral spot that any player is safe to play next to.

After all killed cards have been determined, remove them from play.
After having done so, some cards may have lost allies that were aiding their
defensive values. For this reason, you must go over the cards again, and see if more cards
have no died. Repeat this process this until no more cards are killed.

### An Example

Consider the following game:

<img src="/assets/images/books/formation/figure1.png" alt="figure 1" style="max-width:100%">

Suppose that the
Three of Spades has just flipped face-up, and we are
now looking for casualties. Since this is the only new
card, we only really need to look at it, and adjacent
cards, but let\'s look at all the other cards, too, just to
get a feel for how things work.

The Jack of Spades is being attacked by a
Jack of Diamonds. Since the values are equal, the
Jack of Spades is unharmed.

The Six of Hearts is suffering no assaults.

The Jack of Diamonds is under attack by a
total of 13: 11 from the Jack of Spades, and 2 from
the Two of Clubs. However, the Jack of Diamonds
has a total defense of 15: 11 for the Jack itself, and 4
from the Four of Diamonds. The Six of
Hearts does not add to the Jack\'s defense, since it is not of the
same suit. (Remember: the Ten of Clubs does not aid the Two of
Clubs in its attack &mdash; only in its defense!)

The Two of Clubs has a total defense of 12
with the help of the nearby Ten of Clubs. The Jack of Diamonds attacking it for 11 is not enough.

The Ten of Clubs itself has nothing to worry about.

The Four of Diamonds is backed by the Jack of Diamonds for a total of 15, however it is
under attack by a total of 16: 13 from the King of Spades, and 3 from the Three of Spades. The
Four of Diamonds, therefore, will die, *but not without contributing its value to any attacks on
adjacent cards*.

The Three of Spades, attacked by the Four of Diamonds, has no backup (the Two of
Clubs does not help defend since it is the wrong suit), and consequently will also be killed.

The King of Spades, of course, has nothing to worry about from a Four of Diamonds.

Having checked every card, the Four of Diamonds and the Three of Clubs are
killed, and removed from the game:

<img src="/assets/images/books/formation/figure2.png" alt="figure 2" style="max-width:100%">

But we\'re not done yet!

After those two cards are killed, look back over the remaining cards, and
you\'ll see that the Jack of Diamonds is now in
trouble! With the Four of Diamonds dead, the Jack
of Spades and the Two of Clubs are enough to
destroy the Jack of Diamonds, so it, too, will be killed.
It\'s the only one this time. After removing it,
there is nothing left to do. The remaining cards are safe.
The check for casualties is now complete, and the game may continue.');
    }

}
