<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/eggBook")
 */
class EggController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/listen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'eggBook/#/listen');

        return $responseService->itemActionSuccess('\*hiiiiiisssss\*
Egg Book.

Written by Author Person.

Narrated by Isabel Hilderley.

\*hiiiiiisssss\*

Chapter 1. A History of Eggs.

\*hiiiiiisssss\*

Eggs are quite easy to come by in modern life, and it\'s easy to forget that, for the vast majority of human existence, all animal products had to be found, hunted, or stolen. Pigs were domesticated around 13,000 BC; sheep followed a couple thousand years later. In about 8,000 BC cattle were domesticated, and it\'s at about this time that it\'s believed pigeons were domesticated, but to serve as messengers rather than as food sources.

It wasn\'t until 6,000-5,000 BC, according to fossil evidence, that chickens were domesticated, in ancient China.

This may feel like a very long time ago to you, but remember that humans have roamed the earth for at least 200,000 years, so it is for only about 5% of human history that have we enjoyed a relative abundance of eggs.

Still, during such a short time, we have come up with many ways to use them. Beyond baking, and simple frying, the now-common egg can be found in foods both crunchy, or creamy; lightly sweet, or powerfully pungent; invented on the other side of the earth, or next door; from thousands of years ago, or practically yesterday.

\*hiiiiiisssss\*

Chapter 2. The Century Egg.

\*hiiiiiisssss\*

If chickens were first domesticated in China 8,000 years ago, then it\'s perhaps appropriate to start with the Century Egg, a Chinese recipe, though it\'s said to have been discovered a mere 600 years ago, during the Ming Dynasty.

This preserved egg is somewhat of an acquired taste; it is, in some languages, called the "horse urine egg", which may give you a sense of its reputation.

But don\'t let this put you off! This egg is best used as a spice, to deepen the flavor of a dish, rather than to be eaten alone. Cinnamon and nutmeg are also quite unpleasant - even dangerous - to eat directly, but most people think fondly of these foods.

To make the Century Egg, you will need:

One Egg, of course.

Charcoal.

Limestone.

Rice husks.

Patience. (If you are lacking in patience, Aging Powder will do.)

\*hiiiiiisssss\*

Chapter 3. Meringues.

\*hiiiiiisssss\*

Let\'s travel, now, to Switzerland, just a couple hundred years later, to the village of Meiringen. It is here, according to some, that Meringues were first invented, however this is not universally agreed upon.

What we know for certain, however, is that in 1604, Lady Elinor Poole Fettiplace published a recipe book containing "white biskit bread", a confection virtually identical to Meringues, and just 88 years later, in 1692, a cookbook published by François Massialot contained a similar recipe, this time bearing the familiar name "Meringues".

And unlike the Century Egg, this is a recipe almost anyone can enjoy. (It certainly has not been named after the urine of any animal, that I\'m aware of.)

Egg.

Sugar.

Cream of Tartar.

\*hiiiiiisssss\*

Chapter 4. Egg Nog.

\*hiiiiiisssss\*

According to Merriam Webster, the word "nog" was first used to refer to a strong ale brewed in Norfolk, England, in 1693, a year after "Meringues" were published in France.

Though traditionally alcoholic, many people today consume an alcohol-free (and much sweeter) version. And don\'t worry too much about the inclusion of raw egg. It\'s estimated that only 1 in every 20,000 eggs are contaminated with Salmonella. Those are about the odds of getting in a car accident for every 20 miles driven. To minimize your risk, don\'t use a cracked egg, and get your brakes checked regularly.

One egg, raw.

Milk.

Sugar.

Season with cinnamon and nutmeg to taste. (But probably not Century Egg.)

But if you\'re craving something alcoholic (and eggy - interesting choice), whip up some Zabaglione from Egg, Sugar, and any fruity Wine.

Chapter 5. French Toast.

\*hiiiiiisssss\*

You might think that while the English were inventing Meringues and Egg Nog, the French were cooking up some toast nearby, however, the history of French Toast is about 2,000 years old, a few hundred years before France proper. During this time, the Roman Empire was busy being sacked several times, by the Senones, the Visigoths, and the Vandals, but this did not stop them from collecting recipes, and sometime during the 1st century, a Roman by the name of Caelius Apicius published a book called <cite>De re coquinaria</cite>, "On the Subject of Cooking".

In his book, Caelius Apicius describes "another sweet dish" as follows: "break fine white bread, crust removed, into rather large pieces, soak in milk and eggs, fry in oil, cover with honey, and serve."

As it happens, the modern recipe is not much different:

Slice of Bread. (Do as you will with the crust.)

Milk.

Egg.

Oil (or Butter), for frying.

Top with honey, syrup, or even a simple sprinkling of Sugar and cinnamon. (But probably not Century Egg.)

\*hiiiiiisssss\*

Chapter 6. Custard Tarts.

\*hiiiiiisssss\*

Another invention of Ancient Rome with a French name: "custard" comes from the French word "croustade", which originally referred to the crust of the of tart. (Why the name of the plain crust, and not the sweet filling, survives today is a mystery, at least to me!)

Today, this simple preparation has many variations, and many names: custard tart, Bostom cream pie, Flan Pâtissier. Even the savory quiche shares a history with custard.

For my part, I prefer Flan Pâtissier:

Sugar.

Egg.

Milk.

Pour into a Pie Crust, and bake.

\*hiiiiiisssss\*

Chapter 7. The Fortune Cookie.

\*hiiiiiisssss\*

Our journey brings us, at last, back to Chinese culture, but through a very strange path, in the very-modern day of the eighteen to nineteen hundreds.

In Japan, there is a tradition that survives today of making offerings to Shinto shrines and Buddhist temples, and receiving an o-mikuji - a random fortune written on a strip of paper - in exchange, and in the 1800s, a shrine in Kyoto started placing these fortunes in folded cookies made from a batter containing sesame and miso.

Jump forward nearly 100 years, to the state of California, which, thanks to the United States gold rush, has been receiving a large number of immigrants, including many from Japan. It is here, in the Golden Gate Park\'s Japanese Tea Garden, that the first modern version of the Fortune Cookie (one without sesame or miso) is said to have been sold.

The Fortune Cookie\'s cultural identity changed from Japanese to Chinese sometime during World World II for reasons which are not entirely clear, but today Fortune Cookies continue to be served by Chinese restaurants in the western world.

They are also quite easy to make:

Egg.

Sugar.

Flour.

\*hiiiiiisssss\*

Chapter 7. The Beginning.

\*hiiiiiisssss\*

Statistics says that the longer something has been, the longer you can expect it to be. If humans have been around for 200,000 years, might we expect them to be around for 200,000 more? If so, what other, novel uses might humanity find for the egg during this time? Or, perhaps, just as we\'ve enjoyed eggs for 10,000 years, we should expect to enjoy them for only another 10,000, and if that\'s to be the case, what strange, incredible new food might supplant them?

Join me in the year 12,000 for Egg Book II: The Complete History of Eggs.

Until then.
');
    }
}
