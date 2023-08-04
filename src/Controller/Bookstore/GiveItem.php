<?php
namespace App\Controller\Bookstore;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\BookstoreService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

// allows player to buy books; inventory grows based on various criteria

/**
 * @Route("/bookstore")
 */
class GiveItem extends AbstractController
{
    /**
     * @Route("/giveItem/{item}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function giveItem(
        string $item, BookstoreService $bookstoreService, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Bookstore))
            throw new PSPNotUnlockedException('Bookstore');

        $bookstoreService->advanceBookstoreQuest($user, $item);

        $em->flush();

        $data = $bookstoreService->getResponseData($user);

        $responseService->addFlashMessage('Thanks! Renaming Scrolls now cost ' . $bookstoreService->getRenamingScrollCost($user) . '~~m~~!');

        return $responseService->success($data, [ SerializationGroupEnum::MARKET_ITEM ]);
    }
}
