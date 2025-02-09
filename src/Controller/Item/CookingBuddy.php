<?php
declare(strict_types=1);

namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/item/cookingBuddy")]
class CookingBuddy extends AbstractController
{
    #[Route("/{inventory}/addOrReplace", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function addOrReplace(
        Inventory $inventory, EntityManagerInterface $em, ResponseService $responseService,
        IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cookingBuddy/#/addOrReplace');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::CookingBuddy))
            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::CookingBuddy);

        if($user->getCookingBuddy())
        {
            if($user->getCookingBuddy()->getAppearance() === $inventory->getItem()->getImage())
            {
                $user->getCookingBuddy()->generateNewName($rng);

                $em->remove($inventory);
                $em->flush();

                return $responseService->itemActionSuccess('Your new Cooking Buddy is named "' . $user->getCookingBuddy()->getName() . '".', [ 'itemDeleted' => true ]);
            }
            else
            {
                $user->getCookingBuddy()->setAppearance($inventory->getItem()->getImage());

                $em->remove($inventory);
                $em->flush();

                return $responseService->itemActionSuccess('Your Cooking Buddy\'s appearance has been changed!', [ 'itemDeleted' => true ]);
            }
        }
        else
        {
            $responseText = 'You plug the ' . $inventory->getItem()->getName() . ' into an outlet in your kitchen, and it springs to life! (The Cooking Buddy has been added to your menu!)';

            $cookingBuddy = (new \App\Entity\CookingBuddy())
                ->setOwner($user)
                ->setAppearance($inventory->getItem()->getImage())
                ->generateNewName($rng);

            $em->persist($cookingBuddy);
            $em->remove($inventory);

            $em->flush();

            return $responseService->itemActionSuccess($responseText, [ 'itemDeleted' => true ]);
        }
    }
}