<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/theUmbra")]
class TheUmbraController extends AbstractController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'theUmbra/#/read');

        return $responseService->itemActionSuccess('# The Umbra

## What Is It?

The Umbra has been known by many names, by many people, in many times. Today, most ordinary people would call it "the land of the dead"; a few might call it "the aether"; neither is correct. The Umbra is an infinite expanse containing everything beyond the edge of human belief, but within human comprehension. The further you go, the wilder, and more chaotic the Umbra becomes. There is no hard edge, of course - no line - but as you go deeper into the Umbra, reality begins to fray; it becomes incompatible with existence as we know it. In the Deep Umbra, nothing conceivable by humans could ever exist.

But that is the furthest edge. Closer to us, in the Near Umbra, you will find everything that people forgot, or no longer believe in. Spirits, yes, but other things, too, from lost socks to lycanthropes to old gods no longer followed. Objects there eventually crumble into dust - a blackish sand that makes up the "earth" of the Near Umbra - upon which living creatures walk, build, and live.

## Who Lives There?

Not far into the Near Umbra are cities ruled by powerful entities: gods, dragons, vampire kings, and other ancient beings that were present during the first moments of creation. The dead may linger at the border of the Near Umbra for a while, but eventually they move to these great cities to find new purpose; as we forget them, they forget us.

Besides those things forgotten by humans, there are creatures that travel to the Umbra willingly. Werewolves find sanctuary from humans that would hunt them down. Fairies find it difficult to exist in a world dominated increasingly by logic and order, and so have retreated to the Umbra as well. Even some humans visit the Umbra: wizards, scientists, and artists seeking inspiration that can\'t be found in the "real" world.   

## What Can Be Learned There?

Everything humans forgot or stopped believing in, and everything humans haven\'t yet learned, all dwell in the Umbra in some form: Zeus, star-eating dragons, books containing branches of mathematics not yet discovered, intelligent alien species... the Umbra is infinite, and so is the knowledge it contains. Look long enough, and you will find what you\'re looking for. Look deep enough, and you will learn things you never knew were knowable.

The knowledge of the Umbra seems to be addictive, however: those who stay for a while, learning its secrets, find themselves uninterested in leaving. The sphere of existence we call "The Earth" begins to feel small; its problems, insignificant. Among those wizards, scientists, and artists that live in Umbra long enough, there is a belief: that if you looked long enough, you could surely find a cure to all the world\'s ills, but in the process of learning that cure, you would lose any interest in using it. Why return to that small sphere when the Umbra holds so much more?  

## Channels, Conduits, and Ceremony
 
Though many things have been forgotten by humans, some knowledge has held on through the ages: old traditions passed down from the beginning of time. Song, dance, recipe, ceremony; this knowledge takes many forms. Here are a few common ones:

### Ceremonial Implements

Daggers, knives, and other implements of blood are often involved in ritual. Their purpose holds meaning, and that meaning holds power. Such objects are well suited to housing Umbral forces.

* The Ceremony of Fire gives its invoker the strength and tenacity of fire. Place a piece of Firestone on a ceremonial implement, and bind the two with the threads of creation.
* The Ceremony of Sand and Sea - popular among old sailors and explorers - grants command over nature. Wrap some Silica Grounds in a piece of Seaweed, and the bundle on the ceremonial implement. Bind them with Quintessence. 
* The Ceremony of Shadows allows its invoker to move unseen. I\'m sure I don\'t need to tell you how history has used this ritual. Place a piece of Blackonite on a ceremonial implement, and bind them.

### Summoning

Many historical spells which are said to create something from nothing do so by calling upon creature from the Near Umbra associated with that _something_. The creatures - akin to the Japanese Kami, or Greek Nymph - are easily bribed with Quintessence. The spell and quintessence can often be found bound together in written form, as a scroll. Song and dance are also popular media, but usually require some kind of live sacrifice as a source of Quintessence, making them less preferable.
 
For example, the Red - a fruit with great cultural significance - is a good offering for spirits of fruit in general. It can be bound to ink and Paper for a fruit-summoning scroll, or sacrificed during a fall festival to ensure a good harvest.

Other good offerings include:
* Flowering wheat, for good harvest
* Pure gold and silver, for riches
* Seaweed (as mentioned before)
* Flowers; Rice Flower in particular
* Talons & Wings (be careful with this one!)

Paper is not the only medium to which summoning magic can be bound, of course. There are stories of seeds stained magenta during harvest season, and dark mirrors forged from meteorites. Keep your eyes and mind open, and you will discover many methods of summoning.  

### Food & Drink

* Hallucinogens can grant access to the Umbra, even to those untrained in Arcana. Toadstools are a well-known example; Royal Jelly also has this property.
* A drink known as "Dreamwalker\'s Tea" - made from Ginger, Witch-hazel, and Rice Flower - pushes the drinker\'s sleeping mind to the Dreamscapes, areas of the Umbra which border dreams.
  * Dreamwalker\'s Tea, combined with fruit pie, produces an elixir that causes its drinker to shrink in size!
  * Dreamwalker\'s Tea, combined with plain sugar cookies, produces an elixir that causes its drinker to GROW in size!
* Some food & drink are so imbued with magic, those that consume it may gain their first glimpse of the Umbra. Blood Wine - a fermented drink made from the blood of mythical creatures - is one such item.
* To stave off werecreatures, steep Wolf\'s Bane with a bar of pure silver overnight (or use Aging Powder if you can\'t wait) 
');
    }
}
