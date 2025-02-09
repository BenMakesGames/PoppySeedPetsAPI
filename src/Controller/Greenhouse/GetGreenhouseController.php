<?php
declare(strict_types=1);

namespace App\Controller\Greenhouse;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\PlantTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Repository\InventoryRepository;
use App\Service\GreenhouseService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/greenhouse")]
class GetGreenhouseController extends AbstractController
{
    #[Route("", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getGreenhouse(
        ResponseService $responseService, GreenhouseService $greenhouseService,
        NormalizerInterface $normalizer, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->getGreenhouse())
            throw new PSPNotUnlockedException('Greenhouse');

        $greenhouseService->maybeAssignPollinators($user);

        $data = $normalizer->normalize($greenhouseService->getGreenhouseResponseData($user), null, [
            'groups' => [ SerializationGroupEnum::GREENHOUSE_PLANT, SerializationGroupEnum::MY_GREENHOUSE, SerializationGroupEnum::HELPER_PET ]
        ]);

        if($user->getGreenhouse()->getHasBirdBath())
        {
            $data['hasBubblegum'] = self::hasItemInBirdbath($em, $user, 'Bubblegum');
            $data['hasOil'] = self::hasItemInBirdbath($em, $user, 'Oil');
        }

        return $responseService->success($data);
    }

    private static function hasItemInBirdbath(EntityManagerInterface $em, User $user, string $itemName): bool
    {
        return $em->getRepository(Inventory::class)->count([
            'owner' => $user->getId(),
            'location' => LocationEnum::BIRD_BATH,
            'item' => ItemRepository::getIdByName($em, $itemName)
        ]) > 0;
    }
}
