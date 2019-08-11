<?php
namespace App\Controller;

use App\Entity\GreenhousePlant;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Model\ItemQuantity;
use App\Repository\GreenhousePlantRepository;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class GreenhouseController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getGreenhouse(
        ResponseService $responseService, GreenhousePlantRepository $greenhousePlantRepository,
        InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        if($user->getUnlockedGreenhouse() === null)
            throw new AccessDeniedHttpException('You haven\'t purchased a Greenhouse plot yet.');

        return $responseService->success(
            [
                'plants' => $greenhousePlantRepository->findBy([ 'owner' => $user->getId() ]),
                'fertilizer' => $inventoryRepository->findFertilizers($user),
            ],
            [ SerializationGroupEnum::GREENHOUSE_PLANT ]
        );
    }

    /**
     * @Route("/{plant}/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvestPlant(
        GreenhousePlant $plant, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if(!$plant->getIsAdult() || $plant->getProgress() < 1)
            throw new UnprocessableEntityHttpException('This plant is not yet ready to be harvested.');

        $plant->clearGrowth();

        $quantity = new ItemQuantity();

        $quantity->item = $plant->getPlant()->getItem();
        $quantity->quantity = mt_rand($plant->getPlant()->getMinYield(), $plant->getPlant()->getMaxYield());

        $inventoryService->giveInventory($quantity, $user, $user, $user->getName() . ' grew this in their greenhouse.');

        $userStatsRepository->incrementStat($user, UserStatEnum::HARVESTED_PLANT);

        $em->flush();

        return $responseService->success([ 'item' => $quantity->item->getName(), 'quantity' => $quantity->quantity ]);
    }

    /**
     * @Route("/{plant}/fertilize", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function fertilizePlant(
        GreenhousePlant $plant, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        if($plant->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException();

        if($plant->getLastInteraction() >= (new \DateTimeImmutable())->modify('-12 hours'))
            throw new UnprocessableEntityHttpException('This plant is not yet ready to feed.');

        $fertilizerId = $request->request->getInt('fertilizer', 0);

        $fertilizer = $inventoryRepository->findOneBy([ 'id' => $fertilizerId, 'owner' => $user->getId() ]);

        if(!$fertilizer || $fertilizer->getItem()->getFertilizer() === 0)
            throw new UnprocessableEntityHttpException('A fertilizer must be selected.');

        $plant->increaseGrowth($fertilizer->getItem()->getFertilizer());

        $userStatsRepository->incrementStat($user, UserStatEnum::FERTILIZED_PLANT);

        $em->remove($fertilizer);
        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("/seeds", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getSeeds(ResponseService $responseService, InventoryRepository $inventoryRepository)
    {
        $user = $this->getUser();

        $seeds = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.plant IS NOT NULL')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->getQuery()
            ->getResult()
        ;

        return $responseService->success($seeds, [ SerializationGroupEnum::MY_SEEDS ]);
    }

    /**
     * @Route("/plantSeed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function plantSeed(
        ResponseService $responseService, InventoryRepository $inventoryRepository, Request $request,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        if(count($user->getGreenhousePlants()) >= $user->getMaxPlants())
            throw new UnprocessableEntityHttpException('You can\'t plant anymore plants.');

        $seedId = $request->request->getInt('seed', 0);

        if($seedId <= 0)
            throw new UnprocessableEntityHttpException('"seed" is missing, or invalid.');

        $item = $inventoryRepository->findOneBy([
            'id' => $seedId,
            'owner' => $user->getId(),
        ]);

        if($item === null || $item->getItem()->getPlant() === null)
            throw new NotFoundHttpException('There is no such seed. That\'s super-weird. Can you reload and try again?');

        $plant = (new GreenhousePlant())
            ->setOwner($user)
            ->setPlant($item->getItem()->getPlant())
        ;

        $em->persist($plant);
        $em->remove($item);
        $em->flush();

        return $responseService->success();
    }
}