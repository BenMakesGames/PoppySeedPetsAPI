<?php
namespace App\Controller;

use App\Entity\GreenhousePlant;
use App\Enum\SerializationGroupEnum;
use App\Repository\GreenhousePlantRepository;
use App\Repository\InventoryRepository;
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