<?php
namespace App\Controller\Fireplace;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\DragonRepository;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route("/fireplace")]
class FireplaceController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFireplace(
        InventoryRepository $inventoryRepository, ResponseService $responseService, EntityManagerInterface $em,
        NormalizerInterface $normalizer
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $mantle = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        $dragon = DragonRepository::findWhelp($em, $user);

        return $responseService->success(
            [
                'mantle' => $normalizer->normalize($mantle, null, [ 'groups' => [ SerializationGroupEnum::MY_INVENTORY ] ]),
                'fireplace' => $normalizer->normalize($user->getFireplace(), null, [ 'groups' => [ SerializationGroupEnum::MY_FIREPLACE, SerializationGroupEnum::HELPER_PET ] ]),
                'whelp' => $normalizer->normalize($dragon, null, [ 'groups' => [ SerializationGroupEnum::MY_FIREPLACE ] ]),
            ]
        );
    }

    #[Route("/fuel", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getFireplaceFuel(
        InventoryRepository $inventoryRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace) || !$user->getFireplace())
            throw new PSPNotUnlockedException('Fireplace');

        $fuel = $inventoryRepository->findFuel($user);

        return $responseService->success($fuel, [ SerializationGroupEnum::FIREPLACE_FUEL ]);
    }

    #[Route("/whelpFood", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getWhelpFood(
        ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $whelp = DragonRepository::findWhelp($em, $user);

        if(!$whelp)
            throw new PSPNotUnlockedException('Dragon Whelp');

        $food = $em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->andWhere('i.owner=:user')->setParameter('user', $user->getId())
            ->andWhere('i.location=:home')->setParameter('home', LocationEnum::HOME)
            ->join('i.item', 'item')
            ->join('item.food', 'food')
            ->andWhere('(food.spicy > 0 OR food.meaty > 0 OR food.fishy > 0)')
            ->addOrderBy('item.name', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return $responseService->success($food, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    /**
     * @Route("/mantle/{user}", methods={"GET"}, requirements={"user"="\d+"})
     */
    public function getMantle(User $user, InventoryRepository $inventoryRepository, ResponseService $responseService)
    {
        $inventory = $inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::MANTLE
        ]);

        return $responseService->success($inventory, [ SerializationGroupEnum::FIREPLACE_MANTLE ]);
    }
}
