<?php
namespace App\Controller;

use App\Entity\PetActivityLog;
use App\Enum\BeehiveSpecializationEnum;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Repository\InventoryRepository;
use App\Repository\UserQuestRepository;
use App\Service\BeehiveService;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\ResponseService;
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
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        if(!$user->getUnlockedBeehive() || !$user->getBeehive())
            throw new AccessDeniedHttpException('You haven\'t got a Beehive, yet!');

        $beehive = $user->getBeehive();

        if($beehive->getFlowerPower() > 0)
            throw new UnprocessableEntityHttpException('The colony is still working on the last item you gave them.');

        $itemToFeed = $beehive->getRequestedItem();

        if($inventoryService->loseItem($itemToFeed, $user, LocationEnum::HOME, 1) === 0)
            throw new UnprocessableEntityHttpException('You do not have ' . GrammarFunctions::indefiniteArticle($itemToFeed->getName()) . ' ' . $itemToFeed->getName() . ' in your house.');

        $beehiveService->fedRequestedItem($beehive);
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
        InventoryService $inventoryService
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
                'Fluff', 'Talon', 'Yellow Dye', 'Crooked Stick', 'Glue', 'Sugar', 'Antenna'
            ];

            switch($beehive->getSpecialization())
            {
                case BeehiveSpecializationEnum::FARMING:
                    $possibleItems = array_merge($possibleItems, [
                        'Wheat', 'Blueberries', 'Blackberries', 'Mixed Nuts'
                    ]);
                    break;

                case BeehiveSpecializationEnum::FISHING:
                    $possibleItems = array_merge($possibleItems, [
                        'Fish', 'Fish', 'Fish', 'Fish', 'Scales'
                    ]);
                    break;

                case BeehiveSpecializationEnum::MINING:
                    $possibleItems = array_merge($possibleItems, [
                        'Iron Ore', 'Silver Ore', ArrayFunctions::pick_one([ 'Gold Ore', 'Iron Ore' ])
                    ]);
                    break;
            }

            $item = ArrayFunctions::pick_one($possibleItems);

            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME);

            $itemNames[] = $item;
        }

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        $responseService->addActivityLog((new PetActivityLog())->setEntry('You received ' . ArrayFunctions::list_nice($itemNames) . '.'));

        return $responseService->success($user->getBeehive(), SerializationGroupEnum::MY_BEEHIVE);
    }
}
