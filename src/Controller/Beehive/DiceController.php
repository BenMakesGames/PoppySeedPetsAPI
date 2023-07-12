<?php
namespace App\Controller\Beehive;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Repository\InventoryRepository;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/beehive")
 */
class DiceController extends AbstractController
{
    /**
     * @Route("/dice", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getDice(
        InventoryRepository $inventoryRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new PSPNotUnlockedException('Beehive');

        $inventory = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:home)')
            ->leftJoin('i.item', 'item')
            ->andWhere('item.name IN (:diceItemNames)')
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->setParameter('diceItemNames', array_keys(HollowEarthService::DICE_ITEMS))
            ->getQuery()
            ->getResult()
        ;

        return $responseService->success($inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
