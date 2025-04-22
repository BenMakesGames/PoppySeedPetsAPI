<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\ItemRepository;
use App\Repository\InventoryRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/moth")]
class MothController extends AbstractController
{
    #[Route("/getQuantity/{inventory}", methods: ["GET"])]
    public function getMothInfo(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'releaseMoths');

        if(
            $inventory->getLocation() != LocationEnum::HOME &&
            $inventory->getLocation() != LocationEnum::BASEMENT &&
            $inventory->getLocation() != LocationEnum::MANTLE
        )
        {
            throw new PSPInvalidOperationException('Moths can only be released from the home, basement, or fireplace mantle.');
        }

        $numberOfMoths = InventoryService::countInventory($em, $user->getId(), $inventory->getItem()->getId(), $inventory->getLocation());

        return $responseService->success([
            'location' => $inventory->getLocation(),
            'quantity' => $numberOfMoths
        ]);
    }

    #[Route("/release", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function releaseMoths(
        ResponseService $responseService, UserStatsService $userStatsRepository,
        EntityManagerInterface $em, Request $request, InventoryRepository $inventoryRepository,
        IRandom $rng, InventoryService $inventoryService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $mothCount = $request->request->getInt('count');
        $mothLocation = $request->request->getInt('location');

        if(
            $mothLocation != LocationEnum::HOME &&
            $mothLocation != LocationEnum::BASEMENT &&
            $mothLocation != LocationEnum::MANTLE
        )
        {
            throw new PSPInvalidOperationException('Moths can only be released from the home, basement, or fireplace mantle.');
        }

        if($mothCount == 0)
            throw new PSPFormValidationException('Must release at least one moth!');

        $mothItem = ItemRepository::findOneByName($em, 'Moth');

        $moths = $inventoryRepository->findBy([
            'owner' => $user,
            'item' => $mothItem,
            'location' => $mothLocation
        ], [], $mothCount);

        if(count($moths) != $mothCount)
            throw new PSPNotFoundException('You do not have that many moths to release!');

        foreach($moths as $moth)
            $em->remove($moth);

        $percentChanceOfGreatSuccess = $mothCount;

        $moonPhase = DateFunctions::moonPhase(new \DateTimeImmutable());

        switch($moonPhase)
        {
            case MoonPhaseEnum::NewMoon:
                $percentChanceOfGreatSuccess *= 1 / 2;
                break;
            case MoonPhaseEnum::WaxingCrescent:
            case MoonPhaseEnum::WaningCrescent:
                $percentChanceOfGreatSuccess *= 2 / 3;
                break;
            case MoonPhaseEnum::FirstQuarter:
            case MoonPhaseEnum::LastQuarter:
                $percentChanceOfGreatSuccess *= 3 / 4;
                break;
            case MoonPhaseEnum::WaxingGibbous:
            case MoonPhaseEnum::WaningGibbous:
                $percentChanceOfGreatSuccess *= 4 / 5;
                break;
            case MoonPhaseEnum::FullMoon:
                break;
        }

        $items = [];

        for($i = 0; $i < floor($mothCount / 2); $i++)
        {
            if($rng->rngNextBool())
                $items[] = 'Liquid Ozone';
            else
            {
                $items[] = $rng->rngNextFromArray([
                    'Rock',
                    'Quintessence',
                    'Moon Pearl',
                    'Dark Matter',
                    'Stardust',
                    'Silica Grounds',
                    'Everice',
                ]);
            }
        }

        $gotLove = $rng->rngNextInt(1, 100) <= (int)ceil($percentChanceOfGreatSuccess);

        if($gotLove)
            $items[] = 'Chang\'e\'s Love';

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE, $mothCount);

        $quantitiesByItem = [];

        if(count($items) > 0)
        {
            foreach($items as $item)
            {
                if(array_key_exists($item, $quantitiesByItem))
                    $quantitiesByItem[$item]++;
                else
                    $quantitiesByItem[$item] = 1;

                $inventoryService->receiveItem($item, $user, $user, 'Found by a Moth as it tried to reach the moon...', $mothLocation, false);
            }

            $loot = ArrayFunctions::list_nice_quantities($quantitiesByItem);
        }
        else
            $loot = null;

        if($gotLove)
        {
            if($mothCount == 1)
                $responseService->addFlashMessage('What a lucky moth! It made it all the way to the moon, and was reunited with Chang\'e! You received ' . $loot . '!');
            else
                $responseService->addFlashMessage($mothCount . ' moths went, and one made it all the way to the moon, reunited with Chang\'e! You received ' . $loot . '!');
        }
        else
        {
            if($mothCount == 1)
                $description = 'One moth flew towards the moon, but didn\'t make it...';
            else
                $description = $mothCount . ' moths flew towards the moon, but none made it...';

            if($loot)
                $responseService->addFlashMessage($description . ' You received ' . $loot . ', though, so that\'s something...');
            else
                $responseService->addFlashMessage($description . ' Alas.');
        }

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
