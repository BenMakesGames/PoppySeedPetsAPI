<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Functions\ArrayFunctions;
use App\Repository\InventoryRepository;
use App\Repository\SpiceRepository;
use App\Service\BeehiveService;
use App\Service\HollowEarthService;
use App\Service\InventoryService;
use App\Service\PetAssistantService;
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

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }

    /**
     * @Route("/assignHelper/{pet}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function assignHelper(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em,
        PetAssistantService $petAssistantService
    )
    {
        $user = $this->getUser();

        $petAssistantService->helpBeehive($user, $pet);

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
        ResponseService $responseService, EntityManagerInterface $em, PetAssistantService $petAssistantService,
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

        $extraItem = null;

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
                $petWithSkills = $beehive->getHelper()->getComputedSkills();

                $gathering = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();
                $hunting = $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal();

                $total = $gathering + $hunting;

                if($squirrel3->rngNextInt(1, $total) <= $gathering)
                {
                    $extraItem = $petAssistantService->getExtraItem($gathering,
                        [ 'Tea Leaves', 'Blueberries', 'Blackberries', 'Grandparoot', 'Orange', 'Red' ],
                        [ 'Onion', 'Paper', 'Naner', 'Iron Ore' ],
                        [ 'Gypsum', 'Mixed Nuts', 'Apricot', 'Silver Ore', ],
                        [ 'Gold Ore', 'Liquid-hot Magma' ],
                    );
                }
                else
                {
                    $extraItem = $petAssistantService->getExtraItem($hunting,
                        [ 'Scales', 'Feathers', 'Egg' ],
                        [ 'Toadstool', 'Talon', 'Onion' ],
                        [ 'Toad Legs', 'Jar of Fireflies' ],
                        [ 'Silver Bar', 'Gold Bar', 'Quintessence' ],
                    );
                }

                $newItems[] = $inventoryService->receiveItem($extraItem, $user, $user, $beehive->getHelper()->getName() . ' helped ' . $user->getName() . '\'s bees collect this.', LocationEnum::HOME);
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

        if($extraItem)
            $responseService->addFlashMessage('You received ' . ArrayFunctions::list_nice($itemNames) . '. (The ' . $extraItem . ' was thanks to ' . $beehive->getHelper()->getName() . '\'s help!)');
        else
            $responseService->addFlashMessage('You received ' . ArrayFunctions::list_nice($itemNames) . '.');

        return $responseService->success($user->getBeehive(), [ SerializationGroupEnum::MY_BEEHIVE, SerializationGroupEnum::HELPER_PET ]);
    }
}
