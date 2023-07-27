<?php
namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/inventory")
 */
class MoveController extends AbstractController
{
    /**
     * @Route("/moveTo/{location}", methods={"POST"}, requirements={"location"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function moveInventory(
        int $location, Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository,
        EntityManagerInterface $em
    )
    {
        if(!LocationEnum::isAValue($location))
            throw new PSPFormValidationException('Invalid location given.');

        /** @var User $user */
        $user = $this->getUser();

        $allowedLocations = [ LocationEnum::HOME ];

        if($user->getUnlockedFireplace())
            $allowedLocations[] = LocationEnum::MANTLE;

        if($user->getUnlockedBasement())
            $allowedLocations[] = LocationEnum::BASEMENT;

        if(!in_array($location, $allowedLocations))
            throw new PSPFormValidationException('Invalid location given.');

        $inventoryIds = $request->request->get('inventory');
        if(!\is_array($inventoryIds)) $inventoryIds = [ $inventoryIds ];

        if(count($inventoryIds) >= 200)
            throw new PSPFormValidationException('Oh, goodness, please don\'t try to move more than 200 items at a time. Sorry.');

        /** @var Inventory[] $inventory */
        $inventory = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner=:user')
            ->andWhere('i.id IN (:inventoryIds)')
            ->andWhere('i.location IN (:allowedLocations)')
            ->setParameter('user', $user->getId())
            ->setParameter('inventoryIds', $inventoryIds)
            ->setParameter('allowedLocations', $allowedLocations)
            ->getQuery()
            ->execute()
        ;

        if(count($inventory) !== count($inventoryIds))
            throw new PSPNotFoundException('Some of the items could not be found??');

        $itemsInTargetLocation = $inventoryRepository->countItemsInLocation($user, $location);

        if($location === LocationEnum::HOME)
        {
            if ($itemsInTargetLocation + count($inventory) > User::MAX_HOUSE_INVENTORY)
                throw new PSPInvalidOperationException('You do not have enough space in your house!');
        }

        if($location === LocationEnum::BASEMENT)
        {
            if ($itemsInTargetLocation + count($inventory) > User::MAX_BASEMENT_INVENTORY)
                throw new PSPInvalidOperationException('You do not have enough space in the basement!');
        }

        if($location === LocationEnum::MANTLE)
        {
            if ($itemsInTargetLocation + count($inventory) > $user->getFireplace()->getMantleSize())
                throw new PSPInvalidOperationException('The mantle only has space for ' . $user->getFireplace()->getMantleSize() . ' items.');
        }

        foreach($inventory as $i)
        {
            $i
                ->setLocation($location)
                ->setModifiedOn()
            ;
        }

        $em->flush();

        return $responseService->success();
    }
}
