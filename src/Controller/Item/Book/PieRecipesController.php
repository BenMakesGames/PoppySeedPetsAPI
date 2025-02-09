<?php
declare(strict_types=1);

namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/pieRecipes")]
class PieRecipesController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pieRecipes/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Pie Crust',
            'Blackberry Pie',
            'Blueberry Pie',
            'Chocolate Cream Pie',
            'Chocolate Cream Pie (with Tofu)',
            'Flan Pâtissier',
            'Slice of Pumpkin Pie',
            'Red Pie',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'pieRecipes/#/read');

        return $responseService->itemActionSuccess('Who are you, and how did you come by a copy of my book, I wonder. Did you find it washed up on some strange shore? Pry it from the hands of a hungry Preta? Or perhaps you just bought it from a bookstore?

In what country? In what time? Am I still alive, or have I met some unfortunate end?

I have so many questions, but I\'m sure you have only one: "how can I make pie?"

Whether you found this book on the shores of a Martian beach 2000 years after my death, or bought it from the "New Releases" section of your local bookstore (a point of time during which I very much hope to still be alive), you\'ll no doubt be happy to hear that I can answer your question.

It begins with a crust, builds with a filling, reaches a beautiful crescendo involving all your senses, and finishes - I hope - with a relaxing nap on one of those Martian beaches we\'ve been hearing so much about recently, or if not that, perhaps in the arms of a loved one. (If you can do both, though, definitely do both. I envy you, future human.) 

### Pie Crust

Of pie crusts, there are many options: Graham Cracker. Chocolate Cookie. No crust at all (strange, but true). Personally, I prefer a classic crust:

* Wheat Flour
* Butter

### Filling

I was once told "there as many types of pie filling as there are people who have ever lived, and then some." At first I couldn\'t help but wonder, evolutionarily-speaking, at what point people started to be people, and therefore how many to count, but then I realized that there is a sort of evolution to pie as well. After all, isn\'t Cheesecake more pie than cake? 

#### Berry

* Blueberries or Blackberries
* Wheat Flour
* Sugar

#### Chocolate Cream (traditional)

* Cocoa Powder
* Sugar
* Egg
* Creamy Milk
* Butter (no one said this was going to be good for you; it is beautiful, though)
* Wheat Flour

#### Chocolate Cream (using Tofu)

* Cocoa Powder
* Sugar
* Tofu

#### Flan Pâtissier

* Egg
* Sugar
* Creamy Milk

(Sprinkle some nutmeg on top for an extra touch of class!)

#### Pumpkin Pie

* Pumpkin (any size will do)
* Egg
* Creamy Milk
* Sugar

#### Red

* Red
* Wheat Flour
* Sugar

### Eating

You\'ve made your crust; your filling. You\'ve baked. There\'s only one step left - the hardest, but perhaps most important: where, and with whom, will you eat it?

These are questions I cannot answer, but I believe, without a doubt, that with pie and questions in hand and in mind, you can and will find your own answers. And when you do, consider leaving this book for some future human to find. Place it in the nook of a tree and post its coordinates online, anonymously, without explanation; give it to a niece still too young to read; or just sell it to a used bookstore. Share the slice of life you\'ve experienced through this book as you would a slice of pie.
');
    }
}
