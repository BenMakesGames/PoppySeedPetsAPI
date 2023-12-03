<?php
namespace App\Controller\Dragon;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotFoundException;
use App\Functions\DragonHelpers;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/dragon")]
class GetPotentialOfferingsController extends AbstractController
{
    #[Route("/offerings", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getOfferings(
        ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $dragon = DragonHelpers::getAdultDragon($em, $user);

        if(!$dragon)
            throw new PSPNotFoundException('You don\'t have an adult dragon!');

        $treasures = $inventoryRepository->createQueryBuilder('i')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.treasure', 'treasure')
            ->andWhere('i.owner=:user')
            ->andWhere('i.location=:home')
            ->andWhere('item.treasure IS NOT NULL')
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute()
        ;

        return $responseService->success($treasures, [ SerializationGroupEnum::DRAGON_TREASURE ]);
    }
}
