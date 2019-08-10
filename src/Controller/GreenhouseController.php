<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Repository\GreenhousePlantRepository;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
}