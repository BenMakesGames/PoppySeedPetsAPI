<?php
namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\PlantTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Service\GreenhouseService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/greenhouse")]
class UpdatePlantOrderController extends AbstractController
{
    #[Route("/updatePlantOrder", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function updatePlantOrder(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $greenhouse = $user->getGreenhouse();

        if($greenhouse === null)
            throw new PSPNotUnlockedException('Greenhouse');

        $plantIds = $request->request->all('order');

        $allPlants = $user->getGreenhousePlants();

        $plantIds = array_filter($plantIds, fn(int $i) =>
            ArrayFunctions::any($allPlants, fn(GreenhousePlant $p) => $p->getId() === $i)
        );

        if(count($allPlants) !== count($plantIds))
            throw new PSPFormValidationException('The list of plants must include the full list of your plants; no more; no less!');

        foreach($allPlants as $plant)
        {
            $ordinal = array_search($plant->getId(), $plantIds) + 1;
            $plant->setOrdinal($ordinal);
        }

        $em->flush();

        return $responseService->success();
    }
}
