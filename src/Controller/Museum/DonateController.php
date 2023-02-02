<?php
namespace App\Controller\Museum;

use App\Entity\MuseumItem;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Repository\InventoryRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
        MuseumItemRepository $museumItemRepository, EntityManagerInterface $em, UserStatsRepository $userStatsRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getUnlockedMuseum() === null)
            throw new AccessDeniedHttpException('You have not unlocked this feature yet.');

        $inventoryIds = $request->request->get('inventory');

        if(is_array($inventoryIds) && count($inventoryIds) > 20)
            throw new UnprocessableEntityHttpException('You may only donate up to 20 items at a time.');

        $inventory = $inventoryRepository->findBy([
            'id' => $inventoryIds,
            'owner' => $user,
            'location' => [ LocationEnum::HOME, LocationEnum::BASEMENT ]
        ]);

        if(count($inventory) === 0)
            throw new UnprocessableEntityHttpException('No items were selected.');

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
            throw new UnprocessableEntityHttpException('Some of the selected items could not be donated? That\'s weird. Try reloading and trying again.');

        $totalMuseumPoints = 0;

        foreach($inventory as $i)
        {
            $museumItem = (new MuseumItem())
                ->setUser($user)
                ->setItem($i->getItem())
                ->setCreatedBy($i->getCreatedBy())
                ->setComments($i->getComments())
            ;

            $totalMuseumPoints += $i->getItem()->getMuseumPoints();

            $em->persist($museumItem);
            $em->remove($i);
        }

        $user->addMuseumPoints($totalMuseumPoints);

        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_DONATED_TO_MUSEUM, count($inventory));

        $em->flush();

        return $responseService->success();
    }
}
