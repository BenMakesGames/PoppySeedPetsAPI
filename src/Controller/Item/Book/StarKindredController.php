<?php

namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/starKindred")
 */
class StarKindredController extends AbstractController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'starKindred/#/read');

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::StarKindred))
        {
            UserUnlockedFeatureHelpers::create($em, $user, UnlockableFeatureEnum::StarKindred);

            return $responseService->itemActionSuccess('Looks like a game you can play with your pets! You study the book several times, memorizing every detail... (You can now play ★Kindred with your pets! Find it in the menu!)');
        }
        else
        {
            return $responseService->itemActionSuccess('You already read this book front-to-back several times... there is nothing more to learn from it.');
        }
    }
}