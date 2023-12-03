<?php
namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/riceBook")
 */
class RiceBookController extends AbstractController
{
    private const RECIPES = [
        'Nigiri',
        'Onigiri',
        'Fish Onigiri',
        'Mini Melowatern Onigiri',
        'Plain Fried Rice (A)',
        'Tentacle Fried Rice (A)',
        'Tofu Fried Rice (A)',
        'Vegetable Fried Rice',
        'Rice Vinegar',
        'Simple Sushi',
        'TKG',
        'Tapsilog',
        'Tomato "Sushi"',
        'Yaki Onigiri (A)',
        'Zongzi',
    ];

    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'riceBook/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, self::RECIPES);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'riceBook/#/read');

        return $responseService->itemActionSuccess('# Of Rice

## Table of Contents

* Nigiri
* Onigiri
* Plain Fried Rice
* Rice Flour
* Rice Vinegar
* Simple Sushi
* Tamago Kake Gohan
* Tapsilog (with Fish)
* Tomato "Sushi"
* Yaki Onigiri
* Zongzi

## Recipes

#### Nigiri
* Rice
* Fish

#### Onigiri
A rice ball with a seaweed wrap; hardly anything could be simpler!

Try adding a filling, such as Fish, or Melowatern.

* Rice
* Seaweed

#### Plain Fried Rice

* Rice
* Oil
* Soy Sauce
* Onion
* Egg

Try these simple variants:
* Add Tentacle for Tentacle Fried Rice
* Add Tofu for Tofu Fried Rice
* Add Carrot and Celery for Vegetable Fried Rice

#### Rice Vinegar
An important ingredient when making sushi!

Be sure to include the Aging Powder, or you\'ll just end up with plain ol\' Rice Flour!

* Rice
* Aging Powder

#### Simple Sushi
* Rice
* Sugar
* Vinegar
* Seaweed
* Fish

#### Tamago Kake Gohan
Also known as "TKG"; one of the simplest dishes to make.

* Rice
* Egg
* Soy Sauce

#### Tapsilog (with Fish)
Fried rice, well-cooked fish, and a fried egg. A complete breakfast!

* Onion
* Rice
* Oil
* Fish
* Fried Egg

#### Tomato "Sushi"
* Rice
* Tomato
* Oil
* Soy Sauce

#### Yaki Onigiri
* Onigiri
* Charcoal
* Soy Sauce

#### Zongzi
* Rice
* Fish
* Chanterelle
* Beans
* Mixed Nuts
* Really Big Leaf
* String (for tying it all together!)
');
    }
}
