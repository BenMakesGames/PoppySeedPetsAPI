<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/grandparootSecrets")
 */
class GrandparootSecretsController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'grandparootSecrets/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Aging Powder',
            'Blackberry Wine',
            'Blueberry Wine',
            'Red Wine',
            'Fig Wine',
            'Cheese and Sour Cream',
            'Ginger Beer',
            'Mushrooms',
            'Mushrooms (Crooked Fishing Rod)',
            'Mushrooms (Wooden Sword)',
            'Plain Yogurt',
            'Charcoal (from Torch)',
            'Century Egg',
            'Kombucha (from Sweet Black Tea)',
            'Kombucha (from Black Tea)',
            'Kombucha (from scratch)',
            'Soy Sauce',
            'Kumis',
            'Kilju',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'grandparootSecrets/#/read');

        return $responseService->itemActionSuccess('# Unlocking the Secrets of Grandparoot

Grandparoot - and the Aging Powder which can be extracted from it - is a truly wondrous substance! It makes possible the rapid aging of foods, and other substances; it goes saying that you should always wear gloves when using the stuff! Your hand isn\'t going to instantly fall off if some touches you, but they don\'t say "cooks that work with Grandparoot end up with grandpa hands" for nothing!

How exactly the substance works is beyond the scope of this book (I asked physicist friend; it didn\'t make a lick of sense to me!) but I _can_ tell you how you can use Aging Powder to aid you in the kitchen!
 
### Making Aging Powder

Grandparoot on its own isn\'t very useful raw. The root is exceptionally bitter (which is fine: you definitely shouldn\'t eat it!) and the compounds we need - what we commonly call Aging Powder - are locked up in the fibers of the vegetable.

To get Aging Powder, take your Grandparoot, and simmer it in about 1.5 cups of water in a small saucepan. It\'s important to use enough water to get the process started, but not too much, for reasons we\'ll soon get into.

The heat will break up the fibers, freeing the aging compound inside. It takes a few minutes to really get going, but there\'s a observable turning point where the process will accelerate; the root will begin to shrivel, and your simmer will quickly become a boil as spacetime begins warping inside your saucepan! It\'s important that you be vigilant, and watch for this moment! Once the water begins to boil, turn the heat off. The water will continue to boil, but that\'s okay. As it boils away, the process will slow, and eventually stop.
 
At the bottom of your saucepan, you should be left with a dessicated Grandparoot. There may be some water around the edges of the saucepan; that\'s fine.

The reason for using a particular amount of water is to control the amount of time that the aging process takes place. You want it to run long enough to break apart the root, but not so long that the aging component is completely spent.

It\'s time to separate out the actual Aging Powder!

Remove the desiccated root from the saucepan. Try to avoid contact with any water remaining in the saucepan. We won\'t restart the aging process (heat is needed for that), but the root is easier to work with when dry.

Grind the root in a spice grinder or mortar and pestle.

What you have now is a mixture of ash (burned Grandparoot plant material), and Aging Powder. The Aging Powder is significantly lighter in color; you should be able to see it clearly.
 
Aging Powder is much denser than ash, so it should be very easy to separate by pouring the mixture on one end of a rimmed baking sheet, and gently shaking while holding the pan at a slight angle. The Aging Powder will slide down the sheet much more quickly than the ash, separating out!
 
It\'s okay if you have a few specks of ash in there. If you can still see a lot of Aging Powder in the ash, remove the Aging Powder you were able to separate, and repeat the process.

Congratulations! You now have Aging Powder!

### Wine

Ordinarily, the process of wine-making is a very time-consuming one. Fortunately, Aging Powder makes it possible to quickly make Wine in your own home!
 
Wine experts will tell you that Aging Powder gives the wine a slight metallic taste; I\'ve never been able to taste it, myself, and it\'s certainly cheaper than buying a bottle at the grocery store!

You can make a glass of wine by combining Aging Powder with many fruits, and even some vegetables. Blackberry Wine is one of my favorites!

You will get a small amount of foam around the edge of your glass; scoop that off, but don\'t throw it away! That\'s tartaric acid - dried, it\'s known as Cream of Tartar - very useful in baking!

### Cheese

Of course, if you\'re going to make wine, you _gotta_ make cheese to go with it!

No waiting days for your Creamy Milk to age into cheese! Just add Aging Powder, and you\'re good to go!

### Ginger Beer

Age Ginger root and Sugar with Aging Powder for instant Ginger Beer!

(As a side-note, you _can_ age Sugar by itself, but that\'s less tasty!)

### Mushrooms

You can easily grow a variety of mushrooms on wood with the help of Aging Powder!
 
Take a bit of wood (even a Wooden Sword or fishing rod you\'re no longer using will work!) to a dimly-lit place, dust it with some Aging Powder, et voilà: mushrooms!

### Yogurt

You can\'t get away from the fact that to make Yogurt, you need Yogurt, but you _can_ get away from the time requirement with the application of - you guessed it - Aging Powder!

Take some Yogurt as a starter, and pour in some Creamy Milk. Apply Aging Powder. It\'s as easy as that!

### Charcoal

Of course Charcoal is available from the grocery store, and it\'s not very expensive, but it can be fun to make your own!

If you\'ve got a fireplace, you\'ve probably already incidentally done so... and thrown the stuff away!

For shame! You can use that the next time you want to grill! (Don\'t even talk to me about gas grills!)

If you\'re looking for Charcoal in a pinch, set some wood on fire (or maybe just find an extra Stereotypical Torch you have lying around), place it in something that can withstand the temperature of burning wood, and sprinkle some Aging Powder in!

### Century Egg

Ah! The ever-divisive Century Egg! You either hate it, or you love it; there\'s really no inbetween.

It\'s a bit difficult to prepare. Besides aging, it\'s important that the right chemicals be available, to prevent the egg from spoiling. A Century Egg is not a Rotten Egg!

Besides the Egg and Aging Powder, you will need:
* Charcoal
* Limestone
* Rice (husks)

### Kombucha

I hadn\'t tried Kombucha until I started experimenting with Aging Powder; it\'s a very refreshing drink, but ordinarily takes a few days to make.

You know where this is going!

If you already have Black Tea or Sweet Black Tea made, simply add Aging Powder. (If the tea isn\'t sweetened, you\'ll need to add Sugar as well; Sugar is needed to get the fermenting process going!

If you haven\'t made any tea yet, don\'t worry: Aging Powder will speed up the steeping process as well! Combine Tea Leaves, Sugar, and Aging Powder, and let time do the rest... in just under a minute!

### Soy Sauce

Did you know Soy Sauce is fermented soy beans? Today, starters are available in grocery stores, but the traditional method simply leaves a mixture of beans and wheat flour out to rest.

Subbing Aging Powder for *time itself*, Soy Sauce can be made very quickly indeed! Combine:

* Wheat Flour
* Beans
* Aging Powder

Apply a little heat, and you\'re done!

### Kumis

An ancient fermented drink made from milk! I don\'t know if I believe that Scythians loathed rainbows, but they definitely liked Kumis.

* Creamy Milk
* Sugar (to aid fermentation)
* Aging Powder

### Kilju

Perhaps the simplest fermented drink is Kilju, a Finnish drink. Age some Sugar in water... that\'s literally it!

### Conclusion

There\'s a lot more you can do with Aging Powder, of course, and you should feel free to experiment. I\'ve heard some plants, such as Onion, and Wheat, respond incredibly well to Aging Powder, and even black holes evaporate as they age! I hope this book has opened your eyes to the possibilities that Grandparoot can bring to your kitchen!

Happy cooking!
');
    }
}
