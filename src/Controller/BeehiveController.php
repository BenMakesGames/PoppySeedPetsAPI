<?php
namespace App\Controller;

use App\Enum\BeehiveSpecializationEnum;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\SpiceRepository;
use App\Service\BeehiveService;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/beehive")
 */
class BeehiveController extends PoppySeedPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getBeehive(ResponseService $responseService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);
    }

    /**
     * @Route("/chooseSpecialization", methods={"PATCH"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function chooseSpecialization(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        BeehiveService $beehiveService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $beehive = $user->getBeehive();

        if($beehive->getWorkers() < 2000)
            throw new AccessDeniedHttpException('Your colony is not large enough to choose a specialization.');

        $specialization = $request->request->getAlpha('specialization');

        if(!BeehiveSpecializationEnum::isAValue($specialization))
            throw new UnprocessableEntityHttpException('Please select a specialization.');

        $beehive->setSpecialization($specialization);

        $em->flush();

        return $responseService->success($beehive, SerializationGroupEnum::MY_BEEHIVE);
    }

    /**
     * @Route("/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedItem(
        ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryService $inventoryService, Request $request
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $beehive = $user->getBeehive();

        if($beehive->getFlowerPower() > 0)
            throw new UnprocessableEntityHttpException('The colony is still working on the last item you gave them.');

        $alternate = $request->request->getBoolean('alternate');

        $itemToFeed = $alternate
            ? $beehive->getAlternateRequestedItem()
            : $beehive->getRequestedItem()
        ;

        if($inventoryService->loseItem($itemToFeed, $user, LocationEnum::HOME, 1) === 0)
            throw new UnprocessableEntityHttpException('You do not have ' . $itemToFeed->getNameWithArticle() . ' in your house.');

        $beehiveService->fedRequestedItem($beehive, $alternate);
        $beehive->setInteractionPower();

        $em->flush();

        return $responseService->success($beehive, SerializationGroupEnum::MY_BEEHIVE);
    }

    /**
     * @Route("/reRoll", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function reRollRequest(
        Request $request, ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryRepository $inventoryRepository
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $itemId = $request->request->getInt('die', 0);

        if($itemId < 1)
            throw new UnprocessableEntityHttpException('A die must be selected!');

        $item = $inventoryRepository->find($itemId);

        if(!$item || $item->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('The selected item does not exist! (Reload and try again?)');

        if(!array_key_exists($item->getItem()->getName(), HollowEarthService::DICE_ITEMS))
            throw new UnprocessableEntityHttpException('The selected item is not a die! (Reload and try again?)');

        $em->remove($item);

        $beehiveService->reRollRequest($user->getBeehive());

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);
    }

    /**
     * @Route("/dice", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getDice(
        InventoryRepository $inventoryRepository, ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

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

        return $responseService->success($inventory, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvest(
        ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryService $inventoryService, SpiceRepository $spiceRepository, Squirrel3 $squirrel3
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $beehive = $user->getBeehive();
        $itemNames = [];

        if($beehive->getRoyalJellyPercent() >= 1)
        {
            $beehive->setRoyalJellyProgress(0);

            $inventoryService->receiveItem('Royal Jelly', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);

            $itemNames[] = 'Royal Jelly';
        }

        if($beehive->getHoneycombPercent() >= 1)
        {
            $beehive->setHoneycombProgress(0);

            $inventoryService->receiveItem('Honeycomb', $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);

            $itemNames[] = 'Honeycomb';
        }

        if($beehive->getMiscPercent() >= 1)
        {
            $beehive->setMiscProgress(0);

            $possibleItems = [
                'Fluff', 'Talon', 'Yellow Dye', 'Crooked Stick', 'Glue', 'Sugar', 'Antenna',
                $squirrel3->rngNextFromArray([
                    'Jar of Fireflies', 'Sugar', 'Crooked Stick'
                ])
            ];

            switch($beehive->getSpecialization())
            {
                case BeehiveSpecializationEnum::FARMING:
                    $possibleItems = array_merge($possibleItems, [
                        'Wheat', 'Blueberries', 'Blackberries', 'Mixed Nuts',
                    ]);
                    break;

                case BeehiveSpecializationEnum::FISHING:
                    $possibleItems = array_merge($possibleItems, [
                        'Fish', 'Fish', 'Fish', 'Fish', 'Scales', 'Jar of Fireflies'
                    ]);
                    break;

                case BeehiveSpecializationEnum::MINING:
                    $possibleItems = array_merge($possibleItems, [
                        'Iron Ore', 'Silver Ore', $squirrel3->rngNextFromArray([ 'Gold Ore', 'Iron Ore' ])
                    ]);
                    break;
            }

            $item = $squirrel3->rngNextFromArray($possibleItems);

            $newItem = $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);

            if($newItem->getItem()->getName() === 'Crooked Stick' || $newItem->getItem()->getFood())
            {
                if(mt_rand(1, 20) === 1)
                    $newItem->setSpice($spiceRepository->findOneByName('of Queens'));
                else
                    $newItem->setSpice($spiceRepository->findOneByName('Anthophilan'));
            }

            $itemNames[] = $item;
        }

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        $responseService->addFlashMessage('You received ' . ArrayFunctions::list_nice($itemNames) . '.');

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);
    }
}
