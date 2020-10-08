<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetSkills;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\StoryEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetFactory;
use App\Service\PetService;
use App\Service\ResponseService;
use App\Service\StoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/bug")
 */
class BugController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/squish", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function squishBug(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, UserQuestRepository $userQuestRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/squish');

        $promised = $userQuestRepository->findOrCreate($user, 'Promised to Not Squish Bugs', 0);

        if($promised->getValue())
            return $responseService->itemActionSuccess('You\'ve promised not to squish any more bugs...');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_SQUISHED);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/putOutside", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function putBugOutside(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/putOutside');

        $em->remove($inventory);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_PUT_OUTSIDE);
        $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_RECYCLED);

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/feed", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function feedBug(
        Inventory $inventory, ResponseService $responseService, UserStatsRepository $userStatsRepository,
        EntityManagerInterface $em, Request $request, InventoryRepository $inventoryRepository,
        InventoryService $inventoryService, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'feedBug');

        $item = $inventoryRepository->find($request->request->getInt('food'));

        if(!$item || $item->getOwner()->getId() !== $user->getId())
            throw new UnprocessableEntityHttpException('Must select an item to feed.');

        if(!$item->getItem()->getFood())
            throw new UnprocessableEntityHttpException('Bugs won\'t eat that item. (Bugs are bougie like that, I guess.)');

        switch($inventory->getItem()->getName())
        {
            case 'Centipede':
                $inventory
                    ->changeItem($itemRepository->findOneByName('Moth'))
                    ->addComment($user->getName() . ' fed this Centipede, allowing it to grow up into a beautiful... Moth.')
                    ->setModifiedOn()
                ;
                $message = "What? Centipede is evolving!\n\nCongratulations! Your Centipede evolved into... a Moth??";
                break;

            case 'Cockroach':
                $inventoryService->receiveItem('Cockroach', $user, $user, $user->getName() . ' fed a Cockroach; as a result, _this_ Cockroach showed up. (Is this a good thing?)', $inventory->getLocation());
                $message = 'Oh. You\'ve attracted another Cockroach!';
                break;

            case 'Line of Ants':
                if($item->getItem()->getName() === 'Ants on a Log')
                {
                    if(mt_rand(1, 6) === 6)
                    {
                        $inventoryService->receiveItem('Ant Queen', $user, $user, $user->getName() . ' fed a Line of Ants; as a result, this Queen Ant showed up! (Is this a good thing?)', $inventory->getLocation());
                        $message = 'As part of a study on cannibalism in other species, you feed the Line of Ants some Ants on a Log. And oh: you\'ve attracted the attention of an Ant Queen! (What a surprising result! What could it mean!?)';
                    }
                    else
                    {
                        $inventoryService->receiveItem('Line of Ants', $user, $user, $user->getName() . ' fed a Line of Ants; as a result, _these_ ants showed up. (Is this a good thing?)', $inventory->getLocation());
                        $message = 'As part of a study on cannibalism in other species, you feed the Line of Ants some Ants on a Log. And oh: you\'ve attracted more ants! Interesting... interesting...';
                    }
                }
                else
                {
                    if(mt_rand(1, 6) === 6)
                    {
                        $inventoryService->receiveItem('Ant Queen', $user, $user, $user->getName() . ' fed a Line of Ants; as a result, this Queen Ant showed up! (Is this a good thing?)', $inventory->getLocation());
                        $message = 'Oh? You\'ve attracted an Ant Queen!';
                    }
                    else
                    {
                        $inventoryService->receiveItem('Line of Ants', $user, $user, $user->getName() . ' fed a Line of Ants; as a result, _these_ ants showed up. (Is this a good thing?)', $inventory->getLocation());
                        $message = 'Oh. You\'ve attracted more ants! (You were hoping for an Ant Queen, but oh well... maybe next time...)';
                    }
                }

                break;

            case 'Ant Queen':
                $inventoryService->receiveItem('Line of Ants', $user, $user, $user->getName() . ' fed an Ant Queen; as a result, _these_ ants showed up. (Is this a good thing?)', $inventory->getLocation());
                $message = 'Oh. You\'ve attracted more ants!';
                break;

            case 'Fruit Fly':
                $inventoryService->receiveItem('Fruit Fly', $user, $user, $user->getName() . ' fed a Fruit Fly; as a result, _this_ Fruit Fly showed up. (Is this a good thing?)', $inventory->getLocation());
                $message = 'Oh. You\'ve attracted another Fruit Fly!';
                break;

            case 'Heart Beetle':
                $inventoryService->receiveItem('Heart Beetle', $user, $user, $user->getName() . ' fed a Heart Beetle; as a result, _this_ Heart Beetle showed up. (Is this a good thing?)', $inventory->getLocation());
                $message = 'Oh. You\'ve attracted another Heart Beetle!';
                break;

            default:
                throw new \Exception($inventory->getItem()->getName() . ' cannot be fed! This is totally a programmer\'s error, and should be fixed!');
        }

        $em->remove($item);

        $userStatsRepository->incrementStat($user, UserStatEnum::BUGS_FED);

        $em->flush();

        $responseService->addFlashMessage($message);

        return $responseService->itemActionSuccess(null, [ 'reloadInventory' => true, 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/adopt", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function adopt(
        Inventory $inventory, EntityManagerInterface $em, PetSpeciesRepository $petSpeciesRepository,
        MeritRepository $meritRepository, PetRepository $petRepository, ResponseService $responseService,
        PetFactory $petFactory
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/adopt');

        $petName = ArrayFunctions::pick_one([
            'Afrolixa', 'Alcimus', 'Antocha', 'Argyra', 'Asiola', 'Atarba', 'Atissa',
            'Beskia', 'Bothria', 'Bremia',
            'Cadrema', 'Chlorops', 'Cirrula', 'Cladura', 'Conosia', 'Cremmus',
            'Dagus', 'Dicarca', 'Diostracus', 'Dytomyia',
            'Elliptera', 'Enlinia', 'Eothalassius',
            'Filatopus',
            'Garifuna', 'Gaurax',
            'Harmandia', 'Hurleyella', 'Hyadina',
            'Iteomyia',
            'Janetiella',
            'Lecania', 'Libnotes', 'Lipara',
            'Maietta', 'Mberu', 'Melanderia', 'Meromyza',
            'Nanomyina', 'Narrabeenia', 'Naufraga', 'Neossos',
            'Odus', 'Ormosia', 'Orzihincus',
            'Paraclius', 'Peodes', 'Pilbara', 'Pinyonia', 'Porasilus',
            'Rhaphium', 'Risa',
            'Saphaea', 'Semudobia', 'Shamshevia', 'Silvestrina', 'Stilpnogaster', 'Strobliola', 'Syntormon',
            'Teneriffa', 'Tolmerus', 'Tricimba', 'Trotteria',
            'Vitisiella',
            'Wyliea',
            'Xena',
            'Yumbera',
            'Zeros', 'Zoticus',
        ]);

        // RANDOM!
        $h1 = mt_rand(0, 1000) / 1000.0;
        $s1 = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
        $l1 = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

        $h2 = mt_rand(0, 1000) / 1000.0;
        $s2 = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
        $l2 = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

        $colorA = ColorFunctions::HSL2Hex($h1, $s1, $l1);
        $colorB = ColorFunctions::HSL2Hex($h2, $s2, $l2);

        $newPet = $petFactory->createPet(
            $user,
            $petName,
            $petSpeciesRepository->find(40),
            $colorA,
            $colorB,
            FlavorEnum::getRandomValue(),
            $meritRepository->getRandomAdoptedPetStartingMerit()
        );
        $newPet->setFoodAndSafety(mt_rand(10, 12), -9);

        $numberOfPetsAtHome = $petRepository->getNumberAtHome($user);

        if($numberOfPetsAtHome >= $user->getMaxPets())
        {
            $newPet->setInDaycare(true);
            $message = 'The beetle trundles happily into the daycare...';
            $reloadPets = false;
        }
        else
        {
            $message = 'The beetle finds a nice corner in your house, and settles in...';
            $reloadPets = true;
        }

        $responseService->addFlashMessage($message);

        $em->persist($newPet);
        $em->remove($inventory);
        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true, 'reloadPets' => $reloadPets ]);
    }

    /**
     * @Route("/{inventory}/talkToQueen", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @throws \Exception
     */
    public function talkToQueen(
        Inventory $inventory, StoryService $storyService, Request $request, EntityManagerInterface $em,
        ResponseService $responseService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'bug/#/squish');

        $response = $storyService->doStory($user, StoryEnum::STOLEN_PLANS, $request->request, $inventory);

        return $responseService->success($response, SerializationGroupEnum::STORY);
    }
}
