<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\SpiceRepository;
use App\Service\BeehiveService;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/beehive")
 */
class BeehiveController extends AbstractController
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

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }

    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        PetAssistantService::helpBeehive($user, $pet);

        $em->flush();

        $beehive = $user->getBeehive();

        return $responseService->success($beehive, [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }

    /**
     * @Route("/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedItem(
        ResponseService $responseService, EntityManagerInterface $em, BeehiveService $beehiveService,
        InventoryService $inventoryService, Request $request, ItemRepository $itemRepository
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
        {
            if(!$user->getUnlockedBasement())
                throw new UnprocessableEntityHttpException('You do not have ' . $itemToFeed->getNameWithArticle() . ' in your house!');

            if($inventoryService->loseItem($itemToFeed, $user, LocationEnum::BASEMENT, 1) === 0)
                throw new UnprocessableEntityHttpException('You do not have ' . $itemToFeed->getNameWithArticle() . ' in your house, or your basement!');
            else
                $responseService->addFlashMessage('You give the queen ' . $itemToFeed->getNameWithArticle() . ' from your basement. Her bees immediately whisk it away into the hive!');
        }
        else
            $responseService->addFlashMessage('You give the queen ' . $itemToFeed->getNameWithArticle() . ' from your house. Her bees immediately whisk it away into the hive!');

        $beehiveService->fedRequestedItem($beehive, $alternate);
        $beehive->setInteractionPower();

        $em->flush();

        return $responseService->success($beehive, [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
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

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
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

        return $responseService->success($inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    /**
     * @Route("/harvest", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function harvest(
        ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, SpiceRepository $spiceRepository, Squirrel3 $squirrel3,
        PetActivityLogTagRepository $petActivityLogTagRepository
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
                'Crooked Stick', 'Fluff', 'Yellow Dye', 'Glue', 'Sugar', 'Sugar', 'Sugar', 'Antenna',
            ];

            $item = $squirrel3->rngNextFromArray($possibleItems);

            $newItems = [
                $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' took this from their Beehive.', LocationEnum::HOME)
            ];

            if($beehive->getHelper())
            {
                $helper = $beehive->getHelper();
                $petWithSkills = $helper->getComputedSkills();

                $gathering = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();
                $hunting = $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal();

                $total = $gathering + $hunting;

                $changes = new PetChanges($helper);

                if($total < 2)
                    $doGatherAction = $squirrel3->rngNextBool();
                else
                    $doGatherAction = $squirrel3->rngNextInt(1, $total) <= $gathering;

                if($doGatherAction)
                {
                    $extraItem = PetAssistantService::getExtraItem($squirrel3, $gathering,
                        [ 'Tea Leaves', 'Blueberries', 'Blackberries', 'Grandparoot', 'Orange', 'Red' ],
                        [ 'Onion', 'Paper', 'Naner', 'Iron Ore' ],
                        [ 'Gypsum', 'Mixed Nuts', 'Apricot', 'Silver Ore', ],
                        [ 'Gold Ore', 'Liquid-hot Magma' ],
                    );

                    $verb = 'gather';
                }
                else
                {
                    $extraItem = PetAssistantService::getExtraItem($squirrel3, $hunting,
                        [ 'Scales', 'Feathers', 'Egg' ],
                        [ 'Toadstool', 'Talon', 'Onion' ],
                        [ 'Toad Legs', 'Jar of Fireflies' ],
                        [ 'Silver Bar', 'Gold Bar', 'Quintessence' ],
                    );

                    $verb = 'hunt';
                }

                $activityLog = $responseService->createActivityLog($helper, ActivityHelpers::PetName($helper) . ' helped ' . $user->getName() . '\'s bees while they were out ' . $verb . 'ing, and collected ' . $extraItem . '.', '');

                $inventoryService->petCollectsItem($extraItem, $helper, $helper->getName() . ' helped ' . $user->getName() . '\'s bees ' . $verb . ' this.', $activityLog);

                $activityLog
                    ->addInterestingness(PetActivityLogInterestingnessEnum::PLAYER_ACTION_RESPONSE)
                    ->setChanges($changes->compare($helper))
                    ->addTags($petActivityLogTagRepository->findByNames([ 'Add-on Assistance', 'Beehive' ]))
                ;
            }

            foreach($newItems as $newItem)
            {
                if($newItem->getItem()->getName() === 'Crooked Stick' || $newItem->getItem()->getFood())
                {
                    if($squirrel3->rngNextInt(1, 20) === 1)
                        $newItem->setSpice($spiceRepository->findOneByName('of Queens'));
                    else
                        $newItem->setSpice($spiceRepository->findOneByName('Anthophilan'));
                }

                $itemNames[] = $newItem->getFullItemName();
            }
        }

        $user->getBeehive()->setInteractionPower();

        $em->flush();

        $responseService->addFlashMessage('You received ' . ArrayFunctions::list_nice($itemNames) . '.');

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
