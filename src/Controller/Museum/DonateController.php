<?php
namespace App\Controller\Museum;

use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\PlayerLogHelpers;
use App\Repository\InventoryRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/museum")
 */
class DonateController extends AbstractController
{
    /**
     * @Route("/donate", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function handle(
        ResponseService $responseService, Request $request, InventoryRepository $inventoryRepository,
        MuseumItemRepository $museumItemRepository, EntityManagerInterface $em, UserStatsRepository $userStatsRepository,
        TransactionService $transactionService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            throw new PSPNotUnlockedException('Museum');

        $inventoryIds = $request->request->get('inventory');

        if(is_array($inventoryIds) && count($inventoryIds) > 20)
            throw new PSPFormValidationException('You may only donate up to 20 items at a time.');

        $inventory = $inventoryRepository->findBy([
            'id' => $inventoryIds,
            'owner' => $user,
            'location' => [ LocationEnum::HOME, LocationEnum::BASEMENT ]
        ]);

        if(count($inventory) === 0)
            throw new PSPFormValidationException('No items were selected.');

        for($i = count($inventory) - 1; $i >= 0; $i--)
        {
            if($inventory[$i]->getOwner()->getId() !== $user->getId())
            {
                unset($inventory[$i]);
                continue;
            }

            $existingItem = $museumItemRepository->findOneBy([
                'user' => $user,
                'item' => $inventory[$i]->getItem()
            ]);

            if($existingItem)
            {
                unset($inventory[$i]);
                continue;
            }
        }

        if(count($inventory) === 0)
            throw new PSPNotFoundException('Some of the selected items could not be found or donated? That\'s weird. Try reloading and trying again.');

        $totalMuseumPoints = 0;
        $donatedItemNames = [];

        foreach($inventory as $i)
        {
            $museumItem = (new MuseumItem())
                ->setUser($user)
                ->setItem($i->getItem())
                ->setCreatedBy($i->getCreatedBy())
                ->setComments($i->getComments())
            ;

            $totalMuseumPoints += $i->getItem()->getMuseumPoints();
            $donatedItemNames[] = $i->getItem()->getNameWithArticle();

            $em->persist($museumItem);
            $em->remove($i);
        }

        $donationSummary = count($inventory) > 5 ? (count($inventory) . ' items') : ArrayFunctions::list_nice($donatedItemNames);

        $transactionService->getMuseumFavor($user, $totalMuseumPoints, 'You donated ' . $donationSummary . ' to the Museum.');

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, count($inventory));

        $em->flush();

        return $responseService->success();
    }
}
