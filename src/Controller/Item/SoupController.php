<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/SOUP")
 */
class SoupController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/READ", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function read(Inventory $inventory, ResponseService $responseService)
    {
        $this->validateInventory($inventory, 'SOUP/#/READ');

        return $responseService->itemActionSuccess('# SOUP

#### 15-BEAN SOUP

* FIFTEEN BEANS

IF YOU HAPPEN TO STOR-- \*HACK; CoUgh; wheeze\* to store your Beans in threes, five lots of three will do.

#### Chicken Noodle Soup

* Chicken
* Mirepoix
* Noodles

Also good with Fish, instead of chicken.

#### Fish Stew

* Fish
* Mirepoix
* Tomato
* Spicy Peps (optional)

#### Hobak-Juk

* Rice Flour
* Pumpkin
* Beans

#### Tomato Soup

* Butter
* Onion
* Tomato');
    }
}