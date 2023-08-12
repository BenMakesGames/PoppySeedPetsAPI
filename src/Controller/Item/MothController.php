<?php
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
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/moth")
 */
class MothController extends AbstractController
{
    /**
     * @Route("/getQuantity/{inventory}", methods={"GET"})
     */
    public function getMothInfo(
        Inventory $inventory, ResponseService $responseService,
        InventoryService $inventoryService
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

        $numberOfMoths = $inventoryService->countInventory($user, $inventory->getItem(), $inventory->getLocation());

        return $responseService->success([
            'location' => $inventory->getLocation(),
            'quantity' => $numberOfMoths
        ]);
    }

    /**
     * @Route("/release", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function releaseMoths(
        ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, Request $request, InventoryRepository $inventoryRepository,
        Squirrel3 $rng, InventoryService $inventoryService, ItemRepository $itemRepository
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

        $mothItem = $itemRepository->findOneByName('Moth');

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
            case MoonPhaseEnum::NEW_MOON:
                $percentChanceOfGreatSuccess *= 1 / 2;
                break;
            case MoonPhaseEnum::WAXING_CRESCENT:
            case MoonPhaseEnum::WANING_CRESCENT:
                $percentChanceOfGreatSuccess *= 2 / 3;
                break;
            case MoonPhaseEnum::FIRST_QUARTER:
            case MoonPhaseEnum::LAST_QUARTER:
                $percentChanceOfGreatSuccess *= 3 / 4;
                break;
            case MoonPhaseEnum::WAXING_GIBBOUS:
            case MoonPhaseEnum::WANING_GIBBOUS:
                $percentChanceOfGreatSuccess *= 4 / 5;
                break;
            case MoonPhaseEnum::FULL_MOON:
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

        $gotLove = $rng->rngNextInt(1, 100) <= ceil($percentChanceOfGreatSuccess);

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
