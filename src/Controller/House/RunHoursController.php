<?php
namespace App\Controller\House;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Service\HouseService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/house")]
class RunHoursController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/runHours", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function runHours(
        ResponseService $responseService, HouseService $houseService, EntityManagerInterface $em, LoggerInterface $logger,
        NormalizerInterface $normalizer
    )
    {
        /** @var User $user */
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

            $inventory = $em->getRepository(Inventory::class)->findBy([
                'owner' => $this->getUser(),
                'location' => LocationEnum::HOME
            ]);

            $data['inventory'] = $normalizer->normalize($inventory, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]);
        }

        if($responseService->getReloadPets())
        {
            $responseService->setReloadPets(false);

            $petsAtHome = $em->getRepository(Pet::class)->findBy([
                'owner' => $user->getId(),
                'location' => PetLocationEnum::HOME,
            ]);

            $data['pets'] = $normalizer->normalize($petsAtHome, null, [ 'groups' => [ SerializationGroupEnum::MY_PET ] ]);
        }

        return $responseService->success($data);
    }
}
