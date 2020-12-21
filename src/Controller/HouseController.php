<?php
namespace App\Controller;

use App\Annotations\DoesNotRequireHouseHours;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Service\HouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/house")
 */
class HouseController extends PoppySeedPetsController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/runHours", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function runHours(
        ResponseService $responseService, HouseService $houseService, EntityManagerInterface $em, LoggerInterface $logger,
        PetRepository $petRepository, InventoryRepository $inventoryRepository, NormalizerInterface $normalizer
    )
    {
        $user = $this->getUser();

        try
        {
            $houseService->run($user);
            $em->flush();
        }
        catch(\Doctrine\DBAL\Driver\PDO\Exception $e)
        {
            // hide serialization deadlocks from the end-user, in this case:
            if($e->getCode() === 1213)
                $logger->warning($e->getMessage(), [ 'trace' => $e->getTraceAsString() ]);
            else
                throw $e;
        }

        $data = [];

        if($responseService->getReloadInventory())
        {
            $responseService->setReloadInventory(false);

            $inventory = $inventoryRepository->findBy([
                'owner' => $this->getUser(),
                'location' => LocationEnum::HOME
            ]);

            $data['inventory'] = $normalizer->normalize($inventory, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]);
        }

        if($responseService->getReloadPets())
        {
            $responseService->setReloadPets(false);

            $petsAtHome = $petRepository->findBy([
                'owner' => $user->getId(),
                'inDaycare' => false,
            ]);

            $data['pets'] = $normalizer->normalize($petsAtHome, null, [ 'groups' => [ SerializationGroupEnum::MY_PET ] ]);
        }

        return $responseService->success($data);
    }
}
