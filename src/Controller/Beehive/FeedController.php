<?php
namespace App\Controller\Beehive;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\UserStatsRepository;
use App\Service\BeehiveService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/beehive")
 */
class FeedController extends AbstractController
{
    /**
     * @Route("/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedItem(
        ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryService $inventoryService, Request $request, UserStatsRepository $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive) || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $beehive = $user->getBeehive();

        if($beehive->getFlowerPower() > 0)
            throw new PSPInvalidOperationException('The colony is still working on the last item you gave them.');

        $alternate = $request->request->getBoolean('alternate');

        $itemToFeed = $alternate
            ? $beehive->getAlternateRequestedItem()
            : $beehive->getRequestedItem()
        ;

        if($inventoryService->loseItem($user, $itemToFeed->getId(), LocationEnum::HOME, 1) === 0)
        {
            if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Beehive))
                throw new PSPNotFoundException('You do not have ' . $itemToFeed->getNameWithArticle() . ' in your house!');

            if($inventoryService->loseItem($user, $itemToFeed->getId(), LocationEnum::BASEMENT, 1) === 0)
                throw new PSPNotFoundException('You do not have ' . $itemToFeed->getNameWithArticle() . ' in your house, or your basement!');
            else
                $responseService->addFlashMessage('You give the queen ' . $itemToFeed->getNameWithArticle() . ' from your basement. Her bees immediately whisk it away into the hive!');
        }
        else
            $responseService->addFlashMessage('You give the queen ' . $itemToFeed->getNameWithArticle() . ' from your house. Her bees immediately whisk it away into the hive!');

        $beehiveService->fedRequestedItem($beehive, $alternate);
        $beehive->setInteractionPower();

        $userStatsRepository->incrementStat($user, UserStatEnum::FED_THE_BEEHIVE);

        $em->flush();

        return $responseService->success($beehive, [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
