<?php
namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Enum\PlantTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Service\GreenhouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/greenhouse")
 */
class PlantSeedController extends AbstractController
{
    /**
     * @Route("/plantSeed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function plantSeed(
        ResponseService $responseService, InventoryRepository $inventoryRepository, Request $request,
        EntityManagerInterface $em, GreenhouseService $greenhouseService
    )
    {
        $user = $this->getUser();
        $greenhouse = $user->getGreenhouse();

        if($greenhouse === null)
            throw new AccessDeniedHttpException('You don\'t have a greenhouse!');

        $seedId = $request->request->getInt('seed', 0);

        if($seedId <= 0)
            throw new UnprocessableEntityHttpException('"seed" is missing, or invalid.');

        $seed = $inventoryRepository->findOneBy([
            'id' => $seedId,
            'owner' => $user->getId(),
            'location' => Inventory::CONSUMABLE_LOCATIONS,
        ]);

        if($seed === null || $seed->getItem()->getPlant() === null)
            throw new NotFoundHttpException('There is no such seed. That\'s super-weird. Can you reload and try again?');

        $largestOrdinal = ArrayFunctions::max($user->getGreenhousePlants(), fn(GreenhousePlant $gp) => $gp->getOrdinal());
        $lastOrdinal = $largestOrdinal === null ? 1 : ($largestOrdinal->getOrdinal() + 1);

        $plantsOfSameType = $user->getGreenhousePlants()->filter(fn(GreenhousePlant $plant) =>
            $plant->getPlant()->getType() === $seed->getItem()->getPlant()->getType()
        );

        switch($seed->getItem()->getPlant()->getType())
        {
            case PlantTypeEnum::EARTH: $numberOfPlots = $greenhouse->getMaxPlants(); break;
            case PlantTypeEnum::WATER: $numberOfPlots = $greenhouse->getMaxWaterPlants(); break;
            case PlantTypeEnum::DARK: $numberOfPlots = $greenhouse->getMaxDarkPlants(); break;
            default: throw new \Exception('Selected item doesn\'t have a valid plant type! Someone let Ben know he messed up!');
        }

        if(count($plantsOfSameType) >= $numberOfPlots)
            throw new UnprocessableEntityHttpException('You can\'t plant anymore plants of this type.');

        $plant = (new GreenhousePlant())
            ->setOwner($user)
            ->setPlant($seed->getItem()->getPlant())
            ->setOrdinal($lastOrdinal + 1)
        ;

        $em->persist($plant);
        $em->remove($seed);
        $em->flush();

        return $responseService->success(
            $greenhouseService->getGreenhouseResponseData($user),
            [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        );
    }
}
