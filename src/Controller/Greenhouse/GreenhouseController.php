<?php
namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PlantTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class GreenhouseController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getGreenhouse(
        ResponseService $responseService, GreenhouseService $greenhouseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        $greenhouseService->maybeAssignPollinators($user);

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }

    /**
     * @Route("/seeds/{type}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getSeeds(
        ResponseService $responseService, InventoryRepository $inventoryRepository,
        string $type = PlantTypeEnum::EARTH
    )
    {
        if(!PlantTypeEnum::isAValue($type))
            throw new UnprocessableEntityHttpException('Must provide a valid seed type ("earth", "water", etc...)');

        $user = $this->getUser();

        $seeds = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:consumableLocations)')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.plant', 'plant')
            ->andWhere('item.plant IS NOT NULL')
            ->andWhere('plant.type=:plantType')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('consumableLocations', Inventory::CONSUMABLE_LOCATIONS)
            ->setParameter('plantType', $type)
            ->getQuery()
            ->getResult()
        ;

        return $responseService->success($seeds, [ SerializationGroupEnum::MY_SEEDS ]);
    }

    /**
     * @Route("/updatePlantOrder", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function updatePlantOrder(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $greenhouse = $user->getGreenhouse();

        if($greenhouse === null)
            throw new PSPNotUnlockedException('Greenhouse');

        $plantIds = $request->request->get('order');

        if(!is_array($plantIds))
            throw new UnprocessableEntityHttpException('Must provide a list of plant ids, in the order you wish to save them in.');

        $allPlants = $user->getGreenhousePlants();

        $plantIds = array_filter($plantIds, fn(int $i) =>
            ArrayFunctions::any($allPlants, fn(GreenhousePlant $p) => $p->getId() === $i)
        );

        if(count($allPlants) !== count($plantIds))
            throw new UnprocessableEntityHttpException('The list of plants must include the full list of your plants; no more; no less!');

        foreach($allPlants as $plant)
        {
            $ordinal = array_search($plant->getId(), $plantIds) + 1;
            $plant->setOrdinal($ordinal);
        }

        $em->flush();

        return $responseService->success();
    }
}
